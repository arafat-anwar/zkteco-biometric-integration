<?php

namespace Modules\Credentials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Device;
use Yajra\DataTables\Facades\DataTables;

class DeviceController extends Controller
{
    protected function authorizeManager(): void
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        return view('credentials::devices.index', compact('organizations'));
    }

    public function data(Request $request)
    {
        $this->authorizeManager();

        $query = Device::with('organization')
            ->select(['id','organization_id','name','ip_address','port','connection_type','location','is_active','last_synced_at']);

        if ($orgId = $request->get('organization_id')) {
            $query->where('organization_id', $orgId);
        }

        return DataTables::eloquent($query)
            ->addColumn('org_name', fn ($d) => $d->organization->name ?? '—')
            ->addColumn('connection', fn ($d) => $d->ip_address.':'.$d->port.' ('.$d->connection_type.')')
            ->addColumn('status_badge', fn ($d) => $d->is_active
                ? '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>'
                : '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-red-100 text-red-600 border-red-200">Inactive</span>')
            ->addColumn('synced', fn ($d) => $d->last_synced_at ? $d->last_synced_at->diffForHumans() : 'Never')
            ->addColumn('actions', function ($d) {
                $edit = '<a href="'.route('devices.edit', $d).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('devices.destroy', $d).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this device?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$edit.$del.'</div>';
            })
            ->filterColumn('org_name', fn ($q, $k) => $q->whereHas('organization', fn ($r) => $r->where('name', 'like', "%{$k}%")))
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->get();
        return view('credentials::devices.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name'            => ['required', 'string', 'max:255'],
            'ip_address'      => ['required', 'ip'],
            'port'            => ['required', 'integer', 'min:1', 'max:65535'],
            'serial_number'   => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'location'        => ['nullable', 'string', 'max:255'],
            'mdb_path'        => ['nullable', 'string', 'max:500'],
            'connection_type' => ['required', 'in:tcp,odbc'],
        ]);

        Device::create($data);

        return redirect()->route('devices.index')->with('success', 'Device added successfully.');
    }

    public function show(Device $device)
    {
        $this->authorizeManager();
        $device->load('organization', 'pushLogs');
        $recentEntries = $device->attendanceEntries()->latest('check_time')->take(20)->get();
        return view('credentials::devices.show', compact('device', 'recentEntries'));
    }

    public function edit(Device $device)
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->get();
        return view('credentials::devices.edit', compact('device', 'organizations'));
    }

    public function update(Request $request, Device $device)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name'            => ['required', 'string', 'max:255'],
            'ip_address'      => ['required', 'ip'],
            'port'            => ['required', 'integer', 'min:1', 'max:65535'],
            'serial_number'   => ['nullable', 'string', 'max:100'],
            'model'           => ['nullable', 'string', 'max:100'],
            'location'        => ['nullable', 'string', 'max:255'],
            'mdb_path'        => ['nullable', 'string', 'max:500'],
            'connection_type' => ['required', 'in:tcp,odbc'],
            'is_active'       => ['boolean'],
        ]);

        $device->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('devices.index')->with('success', 'Device updated.');
    }

    public function destroy(Device $device)
    {
        $this->authorizeManager();
        $device->delete();
        return redirect()->route('devices.index')->with('success', 'Device deleted.');
    }
}
