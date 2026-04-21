<?php

namespace Modules\Receiver\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Branch;
use Modules\Credentials\Models\Device;
use Modules\Credentials\Models\Employee;
use Modules\Receiver\Models\AttendanceEntry;
use Yajra\DataTables\Facades\DataTables;

class AttendanceEntryController extends Controller
{
    public function index(Request $request)
    {
        $user          = auth()->user();
        $organizations = $user->isAdmin()
            ? Organization::orderBy('name')->get()
            : $user->organizations()->orderBy('name')->get();
        $devices = Device::orderBy('name')->get();

        return view('receiver::entries.index', compact('organizations', 'devices'));
    }

    public function data(Request $request)
    {
        $user  = auth()->user();
        $query = AttendanceEntry::with(['organization', 'device'])
            ->select(['id','organization_id','device_id','emp_id','real_emp_id','check_time','branch','device_name']);

        if (!$user->isAdmin()) {
            $query->whereIn('organization_id', $user->organizations->pluck('id'));
        }
        if ($orgId = $request->get('organization_id')) {
            $query->where('organization_id', $orgId);
        }
        if ($deviceId = $request->get('device_id')) {
            $query->where('device_id', $deviceId);
        }
        if ($empId = $request->get('emp_id')) {
            $query->where('emp_id', 'like', "%{$empId}%");
        }
        if ($from = $request->get('from')) {
            $query->whereDate('check_time', '>=', $from);
        }
        if ($to = $request->get('to')) {
            $query->whereDate('check_time', '<=', $to);
        }

        return DataTables::eloquent($query->orderByDesc('check_time'))
            ->addColumn('org_name',    fn ($e) => $e->organization->name ?? '—')
            ->addColumn('device_col',  fn ($e) => $e->device->name ?? $e->device_name ?? '—')
            ->addColumn('branch_col',  fn ($e) => $e->branch ?? '—')
            ->addColumn('actions', function ($e) {
                $edit = '<a href="'.route('entries.edit', $e).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('entries.destroy', $e).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this entry?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$edit.$del.'</div>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }

    public function create()
    {
        $user          = auth()->user();
        $organizations = $user->isAdmin()
            ? Organization::where('is_active', true)->orderBy('name')->get()
            : $user->organizations()->where('is_active', true)->orderBy('name')->get();
        $devices    = Device::where('is_active', true)->orderBy('name')->get();
        $employees  = Employee::where('is_active', true)->orderBy('name')->get();
        $branches   = Branch::where('is_active', true)->orderBy('name')->get();

        return view('receiver::entries.create', compact('organizations', 'devices', 'employees', 'branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'device_id'       => ['nullable', 'exists:devices,id'],
            'branch_id'       => ['nullable', 'exists:branches,id'],
            'emp_id'          => ['required', 'string', 'max:50'],
            'real_emp_id'     => ['nullable', 'string', 'max:50'],
            'check_time'      => ['required', 'date'],
            'device_name'     => ['nullable', 'string', 'max:255'],
        ]);

        // Auto-populate branch text from selected branch record
        if (!empty($data['branch_id'])) {
            $data['branch'] = Branch::find($data['branch_id'])?->name;
        }

        // Duplicate check
        $exists = AttendanceEntry::where([
            'organization_id' => $data['organization_id'],
            'device_id'       => $data['device_id'] ?? null,
            'emp_id'          => $data['emp_id'],
            'check_time'      => $data['check_time'],
        ])->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A duplicate entry already exists for this employee at this time.');
        }

        AttendanceEntry::create($data);

        return redirect()->route('entries.index')->with('success', 'Attendance entry created.');
    }

    public function edit(AttendanceEntry $entry)
    {
        $user          = auth()->user();
        $organizations = $user->isAdmin()
            ? Organization::where('is_active', true)->orderBy('name')->get()
            : $user->organizations()->where('is_active', true)->orderBy('name')->get();
        $devices   = Device::where('is_active', true)->orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->get();
        $branches  = Branch::where('is_active', true)->orderBy('name')->get();

        return view('receiver::entries.edit', compact('entry', 'organizations', 'devices', 'employees', 'branches'));
    }

    public function update(Request $request, AttendanceEntry $entry)
    {
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'device_id'       => ['nullable', 'exists:devices,id'],
            'branch_id'       => ['nullable', 'exists:branches,id'],
            'emp_id'          => ['required', 'string', 'max:50'],
            'real_emp_id'     => ['nullable', 'string', 'max:50'],
            'check_time'      => ['required', 'date'],
            'device_name'     => ['nullable', 'string', 'max:255'],
        ]);

        // Auto-populate branch text from selected branch record
        if (!empty($data['branch_id'])) {
            $data['branch'] = Branch::find($data['branch_id'])?->name;
        } else {
            $data['branch'] = null;
        }

        // Duplicate check excluding self
        $exists = AttendanceEntry::where([
            'organization_id' => $data['organization_id'],
            'device_id'       => $data['device_id'] ?? null,
            'emp_id'          => $data['emp_id'],
            'check_time'      => $data['check_time'],
        ])->where('id', '!=', $entry->id)->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A duplicate entry already exists for this employee at this time.');
        }

        $entry->update($data);

        return redirect()->route('entries.index')->with('success', 'Entry updated.');
    }

    public function destroy(AttendanceEntry $entry)
    {
        $entry->delete();
        return redirect()->route('entries.index')->with('success', 'Entry deleted.');
    }
}
