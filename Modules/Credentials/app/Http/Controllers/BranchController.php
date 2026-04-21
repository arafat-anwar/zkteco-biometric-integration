<?php

namespace Modules\Credentials\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Authentication\Models\Organization;
use Modules\Credentials\Models\Branch;
use Yajra\DataTables\Facades\DataTables;

class BranchController extends Controller
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
        return view('credentials::branches.index', compact('organizations'));
    }

    public function data(Request $request)
    {
        $this->authorizeManager();

        $query = Branch::with('organization')
            ->select(['id','organization_id','name','location','is_active']);

        if ($orgId = $request->get('organization_id')) {
            $query->where('organization_id', $orgId);
        }

        return DataTables::eloquent($query)
            ->addColumn('org_name', fn ($b) => $b->organization->name ?? '—')
            ->addColumn('location_text', fn ($b) => $b->location ?? '—')
            ->addColumn('status_badge', fn ($b) => $b->is_active
                ? '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>'
                : '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-red-100 text-red-600 border-red-200">Inactive</span>')
            ->addColumn('actions', function ($b) {
                $edit = '<a href="'.route('branches.edit', $b).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('branches.destroy', $b).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this branch?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$edit.$del.'</div>';
            })
            ->filterColumn('org_name', fn ($q, $k) => $q->whereHas('organization', fn ($r) => $r->where('name', 'like', "%{$k}%")))
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('credentials::branches.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $this->authorizeManager();

        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name'            => ['required', 'string', 'max:255'],
            'location'        => ['nullable', 'string', 'max:255'],
            'is_active'       => ['boolean'],
        ]);

        $exists = Branch::where('organization_id', $data['organization_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'A branch with this name already exists in the selected organization.');
        }

        Branch::create([...$data, 'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully.');
    }

    public function show(Branch $branch)
    {
        $this->authorizeManager();
        $branch->load('organization');

        return view('credentials::branches.show', compact('branch'));
    }

    public function edit(Branch $branch)
    {
        $this->authorizeManager();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();

        return view('credentials::branches.edit', compact('branch', 'organizations'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorizeManager();

        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'name'            => ['required', 'string', 'max:255'],
            'location'        => ['nullable', 'string', 'max:255'],
            'is_active'       => ['boolean'],
        ]);

        $duplicate = Branch::where('organization_id', $data['organization_id'])
            ->where('name', $data['name'])
            ->where('id', '!=', $branch->id)
            ->exists();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Another branch with this name already exists in the selected organization.');
        }

        $branch->update([...$data, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        $this->authorizeManager();
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Branch deleted.');
    }
}
