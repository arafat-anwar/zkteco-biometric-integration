<?php

namespace Modules\Credentials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Employee;
use Yajra\DataTables\Facades\DataTables;

class EmployeeController extends Controller
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
        return view('credentials::employees.index', compact('organizations'));
    }

    public function data(Request $request)
    {
        $this->authorizeManager();

        $query = Employee::with('organization')
            ->select(['id','organization_id','name','badge_number','card_no','privilege','is_active']);

        if ($orgId = $request->get('organization_id')) {
            $query->where('organization_id', $orgId);
        }

        return DataTables::eloquent($query)
            ->addColumn('org_name', fn ($e) => $e->organization->name ?? '—')
            ->addColumn('privilege_label', function ($e) {
                $map = [0 => 'Normal', 1 => 'Enroller', 2 => 'Manager', 3 => 'Admin'];
                $cls = [0 => 'bg-gray-100 text-gray-600', 1 => 'bg-sky-100 text-sky-700', 2 => 'bg-blue-100 text-blue-700', 3 => 'bg-purple-100 text-purple-700'];
                $label = $map[$e->privilege] ?? $e->privilege;
                $color = $cls[$e->privilege] ?? 'bg-gray-100 text-gray-600';
                return '<span class="px-2 py-0.5 text-xs font-medium rounded-full '.$color.'">'.$label.'</span>';
            })
            ->addColumn('status_badge', fn ($e) => $e->is_active
                ? '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>'
                : '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-red-100 text-red-600 border-red-200">Inactive</span>')
            ->addColumn('actions', function ($e) {
                $edit = '<a href="'.route('employees.edit', $e).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('employees.destroy', $e).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this employee?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$edit.$del.'</div>';
            })
            ->filterColumn('org_name', fn ($q, $k) => $q->whereHas('organization', fn ($r) => $r->where('name', 'like', "%{$k}%")))
            ->rawColumns(['privilege_label', 'status_badge', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        return view('credentials::employees.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_user_id'     => ['required', 'integer', 'min:1'],
            'badge_number'    => ['nullable', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:255'],
            'card_no'         => ['nullable', 'string', 'max:100'],
            'department_id'   => ['nullable', 'integer'],
            'privilege'       => ['required', 'integer', 'in:0,1,2,3'],
            'is_active'       => ['boolean'],
        ]);

        $exists = Employee::where('organization_id', $data['organization_id'])
            ->where('mdb_user_id', $data['mdb_user_id'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'An employee with this User ID already exists in the selected organization.');
        }

        Employee::create([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $this->authorizeManager();
        $employee->load('organization');
        return view('credentials::employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        return view('credentials::employees.edit', compact('employee', 'organizations'));
    }

    public function update(Request $request, Employee $employee)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'mdb_user_id'     => ['required', 'integer', 'min:1'],
            'badge_number'    => ['nullable', 'string', 'max:50'],
            'name'            => ['required', 'string', 'max:255'],
            'card_no'         => ['nullable', 'string', 'max:100'],
            'department_id'   => ['nullable', 'integer'],
            'privilege'       => ['required', 'integer', 'in:0,1,2,3'],
            'is_active'       => ['boolean'],
        ]);

        $duplicate = Employee::where('organization_id', $data['organization_id'])
            ->where('mdb_user_id', $data['mdb_user_id'])
            ->where('id', '!=', $employee->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Another employee with this User ID already exists in the selected organization.');
        }

        $employee->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('employees.index')->with('success', 'Employee updated.');
    }

    public function destroy(Employee $employee)
    {
        $this->authorizeManager();
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted.');
    }
}
