<?php

namespace Modules\API\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Credentials\Models\Branch;
use Modules\Credentials\Models\Device;
use Modules\Receiver\Models\AttendanceEntry;
use Modules\Receiver\Models\PushLog;

/**
 * @group Attendance
 */
class AttendanceApiController extends Controller
{
    /**
     * List Attendance Entries
     *
     * Get paginated attendance entries for the authenticated user.
     *
     * @authenticated
     * @queryParam organization_id integer Filter by organization. Example: 1
     * @queryParam device_id integer Filter by device. Example: 1
     * @queryParam branch_id integer Filter by branch. Example: 1
     * @queryParam emp_id string Filter by employee ID. Example: EMP001
     * @queryParam from date Filter from date (Y-m-d). Example: 2026-01-01
     * @queryParam to date Filter to date (Y-m-d). Example: 2026-01-31
     * @queryParam per_page integer Results per page. Example: 50
     *
     * @response 200 {
     *   "success": true,
     *   "data": [],
     *   "meta": {"total": 100, "per_page": 50, "current_page": 1}
     * }
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = AttendanceEntry::with(["organization:id,name", "device:id,name,location", "branch:id,name,location"]);

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
        if ($branchId = $request->get("branch_id")) {
            $query->where("branch_id", $branchId);
        }
        if ($empId = $request->get("emp_id")) {
            $query->where("emp_id", $empId);
        }
        if ($from = $request->get("from")) {
            $query->whereDate("check_time", ">=", $from);
        }
        if ($to = $request->get("to")) {
            $query->whereDate("check_time", "<=", $to);
        }

        $perPage = min((int) $request->get("per_page", 50), 200);
        $entries = $query->orderByDesc("check_time")->paginate($perPage);

        return response()->json([
            "success" => true,
            "data"    => $entries->items(),
            "meta"    => [
                "total"        => $entries->total(),
                "per_page"     => $entries->perPage(),
                "current_page" => $entries->currentPage(),
                "last_page"    => $entries->lastPage(),
            ],
        ]);
    }

    /**
     * Receive Attendance (API Push)
     *
     * Push attendance entries from ZKTeco devices via API.
     * Requires a valid user token AND a push_token in headers.
     *
     * @authenticated
     * @header X-Push-Token string required Push secret token. Example: secret123
     *
     * @bodyParam organization_id integer required Organization ID. Example: 1
     * @bodyParam device_id integer required Device ID. Example: 1
     * @bodyParam entries[].emp_id string required Employee ID from device. Example: EMP001
     * @bodyParam entries[].check_time string required Check time (Y-m-d H:i:s). Example: 2026-01-15 09:00:00
     * @bodyParam entries[].real_emp_id string Real mapped employee ID. Example: 001
     * @bodyParam entries[].branch_id integer Branch ID (from branches table). Example: 1
     * @bodyParam entries[].branch string Branch name (fallback if branch_id not set). Example: HQ
     *
     * @response 200 {
     *   "success": true,
     *   "saved": 10,
     *   "duplicates": 2,
     *   "total": 12
     * }
     */
    public function receive(Request $request)
    {
        $token = $request->header("X-Push-Token");
        if (empty($token) || $token !== config("pusher.push_token")) {
            return response()->json(["message" => "Invalid push token."], 401);
        }

        $data = $request->validate([
            "organization_id"          => ["required", "exists:organizations,id"],
            "device_id"                => ["required", "exists:devices,id"],
            "device_name"              => ["nullable", "string"],
            "entries"                  => ["required", "array"],
            "entries.*.emp_id"         => ["required", "string"],
            "entries.*.real_emp_id"    => ["nullable", "string"],
            "entries.*.check_time"     => ["required", "date_format:Y-m-d H:i:s"],
            "entries.*.device_name"    => ["nullable", "string"],
            "entries.*.branch_id"      => ["nullable", "exists:branches,id"],
            "entries.*.branch"         => ["nullable", "string"],
        ]);

        $saved = 0; $duplicates = 0;

        DB::beginTransaction();
        try {
            foreach ($data["entries"] as $entry) {
                $exists = AttendanceEntry::where([
                    "organization_id" => $data["organization_id"],
                    "device_id"       => $data["device_id"],
                    "emp_id"          => $entry["emp_id"],
                    "check_time"      => $entry["check_time"],
                ])->exists();

                if ($exists) { $duplicates++; continue; }

                $branchId   = $entry["branch_id"] ?? null;
                $branchName = $entry["branch"] ?? null;
                if ($branchId && !$branchName) {
                    $branchName = Branch::find($branchId)?->name;
                }

                AttendanceEntry::create([
                    "organization_id" => $data["organization_id"],
                    "device_id"       => $data["device_id"],
                    "branch_id"       => $branchId,
                    "emp_id"          => $entry["emp_id"],
                    "real_emp_id"     => $entry["real_emp_id"] ?? null,
                    "check_time"      => $entry["check_time"],
                    "device_name"     => $entry["device_name"] ?? $data["device_name"] ?? null,
                    "branch"          => $branchName,
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

            return response()->json(["success" => true, "saved" => $saved, "duplicates" => $duplicates, "total" => count($data["entries"])]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(["success" => false, "message" => $e->getMessage()], 500);
        }
    }

    /**
     * Push Logs
     *
     * Get push history logs for the authenticated user.
     *
     * @authenticated
     * @queryParam organization_id integer Filter by organization. Example: 1
     *
     * @response 200 {"success": true, "data": []}
     */
    public function pushLogs(Request $request)
    {
        $user = $request->user();
        $query = PushLog::with(["organization:id,name", "device:id,name"]);

        if (!$user->isAdmin()) {
            $orgIds = $user->organizations->pluck("id");
            $query->whereIn("organization_id", $orgIds);
        }

        if ($orgId = $request->get("organization_id")) {
            $query->where("organization_id", $orgId);
        }

        $logs = $query->latest()->paginate(30);

        return response()->json([
            "success" => true,
            "data"    => $logs->items(),
            "meta"    => ["total" => $logs->total(), "current_page" => $logs->currentPage()],
        ]);
    }
}
