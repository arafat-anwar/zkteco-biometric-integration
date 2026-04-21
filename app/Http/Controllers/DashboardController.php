<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Device;
use Modules\Receiver\Models\AttendanceEntry;
use Modules\Receiver\Models\PushLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            $orgCount    = Organization::where('is_active', true)->count();
            $deviceCount = Device::where('is_active', true)->count();
            $entryCount  = AttendanceEntry::whereDate('check_time', today())->count();
            $pushCount   = PushLog::whereDate('created_at', today())->count();
        } else {
            $orgIds      = $user->organizations->pluck('id');
            $orgCount    = $orgIds->count();
            $deviceCount = Device::whereIn('organization_id', $orgIds)->where('is_active', true)->count();
            $entryCount  = AttendanceEntry::whereIn('organization_id', $orgIds)->whereDate('check_time', today())->count();
            $pushCount   = PushLog::whereIn('organization_id', $orgIds)->whereDate('created_at', today())->count();
        }

        $recentLogs = PushLog::with(['organization', 'device'])
            ->when(!$user->isAdmin(), fn($q) => $q->whereIn('organization_id', $user->organizations->pluck('id')))
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact('orgCount', 'deviceCount', 'entryCount', 'pushCount', 'recentLogs'));
    }
}
