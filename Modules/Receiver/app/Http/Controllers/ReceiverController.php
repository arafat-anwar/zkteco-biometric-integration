<?php

namespace Modules\Receiver\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Device;
use Modules\Receiver\Models\AttendanceEntry;
use Modules\Receiver\Models\PushLog;

class ReceiverController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = AttendanceEntry::with(["organization", "device"]);

        if (!$user->isAdmin()) {
            $orgIds = $user->organizations->pluck("id");
            $query->whereIn("organization_id", $orgIds);
        }

        if ($orgId = $request->get("organization_id")) {
            $query->where("organization_id", $orgId);
        }
        if ($deviceId = $request->get("device_id")) {
            $query->where("device_id", $deviceId);
        }
        if ($from = $request->get("from")) {
            $query->whereDate("check_time", ">=", $from);
        }
        if ($to = $request->get("to")) {
            $query->whereDate("check_time", "<=", $to);
        }

        $entries = $query->orderByDesc("check_time")->paginate(50);

        $organizations = $user->isAdmin()
            ? Organization::orderBy('name')->get()
            : $user->organizations()->orderBy('name')->get();

        return view("receiver::index", compact("entries", "organizations"));
    }

    public function logs(Request $request)
    {
        $user = auth()->user();
        $query = PushLog::with(["organization", "device"]);

        if (!$user->isAdmin()) {
            $orgIds = $user->organizations->pluck("id");
            $query->whereIn("organization_id", $orgIds);
        }

        $logs = $query->latest()->paginate(30);
        return view("receiver::logs", compact("logs"));
    }

    /**
     * Receive attendance data pushed from Pusher module.
     * Accepts JSON payload with entries array.
     */
    public function receive(Request $request)
    {
        // Validate push token
        $token = $request->header("X-Push-Token");
        if (empty($token) || $token !== config("pusher.push_token")) {
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $data = $request->validate([
            "organization_id"   => ["required", "exists:organizations,id"],
            "device_id"         => ["required", "exists:devices,id"],
            "device_name"       => ["nullable", "string"],
            "entries"           => ["required", "array"],
            "entries.*.emp_id"      => ["required", "string"],
            "entries.*.real_emp_id" => ["nullable", "string"],
            "entries.*.check_time"  => ["required", "date_format:Y-m-d H:i:s"],
            "entries.*.device_name" => ["nullable", "string"],
            "entries.*.branch"      => ["nullable", "string"],
        ]);

        $saved      = 0;
        $duplicates = 0;

        DB::beginTransaction();
        try {
            foreach ($data["entries"] as $entry) {
                $exists = AttendanceEntry::where([
                    "organization_id" => $data["organization_id"],
                    "device_id"       => $data["device_id"],
                    "emp_id"          => $entry["emp_id"],
                    "check_time"      => $entry["check_time"],
                ])->exists();

                if ($exists) {
                    $duplicates++;
                    continue;
                }

                AttendanceEntry::create([
                    "organization_id" => $data["organization_id"],
                    "device_id"       => $data["device_id"],
                    "emp_id"          => $entry["emp_id"],
                    "real_emp_id"     => $entry["real_emp_id"] ?? null,
                    "check_time"      => $entry["check_time"],
                    "device_name"     => $entry["device_name"] ?? $data["device_name"] ?? null,
                    "branch"          => $entry["branch"] ?? null,
                ]);
                $saved++;
            }

            PushLog::create([
                "organization_id"    => $data["organization_id"],
                "device_id"          => $data["device_id"],
                "records_pushed"     => count($data["entries"]),
                "records_saved"      => $saved,
                "duplicates_skipped" => $duplicates,
                "status"             => "success",
                "pusher_ip"          => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                "success"    => true,
                "saved"      => $saved,
                "duplicates" => $duplicates,
                "total"      => count($data["entries"]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            PushLog::create([
                "organization_id"    => $data["organization_id"],
                "device_id"          => $data["device_id"],
                "records_pushed"     => count($data["entries"]),
                "records_saved"      => 0,
                "duplicates_skipped" => 0,
                "status"             => "failed",
                "message"            => $e->getMessage(),
                "pusher_ip"          => $request->ip(),
            ]);

            return response()->json([
                "success" => false,
                "message" => "Failed to save entries: " . $e->getMessage(),
            ], 500);
        }
    }
}
