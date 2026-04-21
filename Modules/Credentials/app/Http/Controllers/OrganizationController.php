<?php

namespace Modules\Credentials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Yajra\DataTables\Facades\DataTables;

class OrganizationController extends Controller
{
    protected function authorizeManager(): void
    {
        if (!auth()->user()->isManager()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        return view('credentials::organizations.index');
    }

    public function data()
    {
        $this->authorizeManager();

        $query = Organization::withCount('devices')
            ->select(['id','name','slug','email','phone','is_active','created_at']);

        return DataTables::eloquent($query)
            ->addColumn('devices_badge', fn ($o) =>
                '<span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">'.$o->devices_count.' device(s)</span>')
            ->addColumn('status_badge', fn ($o) => $o->is_active
                ? '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>'
                : '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-red-100 text-red-600 border-red-200">Inactive</span>')
            ->addColumn('actions', function ($o) {
                $view = '<a href="'.route('organizations.show', $o).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-50 text-gray-700 hover:bg-gray-100 border border-gray-200 transition-colors">View</a>';
                $edit = '<a href="'.route('organizations.edit', $o).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('organizations.destroy', $o).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this organization?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$view.$edit.$del.'</div>';
            })
            ->rawColumns(['devices_badge', 'status_badge', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $this->authorizeManager();
        return view('credentials::organizations.create');
    }

    public function store(Request $request)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'slug'    => ['required', 'string', 'max:100', 'unique:organizations', 'alpha_dash'],
            'email'   => ['nullable', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ]);

        Organization::create($data);

        return redirect()->route('organizations.index')->with('success', 'Organization created successfully.');
    }

    public function show(Organization $organization)
    {
        $this->authorizeManager();
        $organization->load(['devices', 'users']);
        return view('credentials::organizations.show', compact('organization'));
    }

    public function edit(Organization $organization)
    {
        $this->authorizeManager();
        return view('credentials::organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $this->authorizeManager();
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['required', 'string', 'max:100', 'alpha_dash', 'unique:organizations,slug,' . $organization->id],
            'email'     => ['nullable', 'email', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:50'],
            'address'   => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $organization->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('organizations.index')->with('success', 'Organization updated.');
    }

    public function destroy(Organization $organization)
    {
        $this->authorizeManager();
        $organization->delete();
        return redirect()->route('organizations.index')->with('success', 'Organization deleted.');
    }
}
