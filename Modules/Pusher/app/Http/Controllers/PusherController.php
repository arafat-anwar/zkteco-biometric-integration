<?php

namespace Modules\Pusher\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Device;

class PusherController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $organizations = $user->isAdmin()
            ? Organization::with("devices")->where("is_active", true)->get()
            : $user->organizations()->with("devices")->where("is_active", true)->get();
        return view("pusher::index", compact("organizations"));
    }

    public function showDevice(Device $device)
    {
        $this->authorizeDevice($device);
        $pushLogs = $device->pushLogs()->latest()->take(10)->get();
        return view("pusher::device", compact("device", "pushLogs"));
    }

    public function preview(Request $request, Device $device)
    {
        $this->authorizeDevice($device);
        $data = $request->validate([
            "from" => ["required", "date"],
            "to"   => ["required", "date", "after_or_equal:from"],
        ]);
        $fromDate = $data["from"];
        $toDate   = $data["to"];
        $error    = null;
        $entries  = [];
        try {
            $entries = $this->readMdbEntries($device, $fromDate, $toDate);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }
        return view("pusher::preview", compact("device", "entries", "fromDate", "toDate", "error"));
    }

    public function push(Request $request, Device $device)
    {
        $this->authorizeDevice($device);
        $data = $request->validate([
            "from" => ["required", "date"],
            "to"   => ["required", "date", "after_or_equal:from"],
        ]);
        try {
            $entries = $this->readMdbEntries($device, $data["from"], $data["to"]);
            if (empty($entries)) {
                return back()->with("warning", "No entries found for the selected date range.");
            }
            $payload = [
                "organization_id" => $device->organization_id,
                "device_id"       => $device->id,
                "device_name"     => $device->name,
                "entries"         => $entries,
            ];
            $response = Http::withHeaders([
                "Accept"       => "application/json",
                "X-Push-Token" => config("pusher.push_token"),
            ])->post(url("/receiver/receive"), $payload);
            if ($response->successful()) {
                $result = $response->json();
                $device->update(["last_synced_at" => now()]);
                return back()->with("success", "Push successful! Saved: {$result["saved"]}, Duplicates: {$result["duplicates"]}.");
            }
            return back()->with("error", "Receiver returned an error: " . $response->body());
        } catch (\Throwable $e) {
            return back()->with("error", "Push failed: " . $e->getMessage());
        }
    }

    protected function readMdbEntries(Device $device, string $from, string $to): array
    {
        if ($device->connection_type !== "odbc" || empty($device->mdb_path)) {
            throw new \RuntimeException("Device is not configured for MDB/ODBC connection.");
        }
        $dsn = "odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq={$device->mdb_path};";
        $pdo = new \PDO($dsn, "", "");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $userStmt = $pdo->query("SELECT USERID, Badgenumber, Name FROM USERINFO");
        $users = [];
        foreach ($userStmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $users[$row["USERID"]] = $row;
        }
        $stmt = $pdo->prepare("SELECT USERID, CHECKTIME, sn FROM CHECKINOUT WHERE CHECKTIME >= ? AND CHECKTIME <= ? ORDER BY CHECKTIME ASC");
        $stmt->execute([$from . " 00:00:01", $to . " 23:59:59"]);
        $entries = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $userId = $row["USERID"];
            $empId  = isset($users[$userId]) ? $users[$userId]["Badgenumber"] : $userId;
            $entries[] = [
                "emp_id"      => (string) $empId,
                "real_emp_id" => (string) $userId,
                "check_time"  => date("Y-m-d H:i:s", strtotime($row["CHECKTIME"])),
                "device_name" => $device->name,
                "branch"      => $device->location ?? "",
            ];
        }
        return $entries;
    }

    protected function authorizeDevice(Device $device): void
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $orgIds = $user->organizations->pluck("id");
            if (!$orgIds->contains($device->organization_id)) {
                abort(403, "You do not have access to this device.");
            }
        }
    }
}
