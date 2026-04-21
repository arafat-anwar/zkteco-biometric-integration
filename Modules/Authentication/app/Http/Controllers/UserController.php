<?php

namespace Modules\Authentication\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Modules\Authentication\Models\Organization;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected function authorizeAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }
    }

    public function index()
    {
        $this->authorizeAdmin();
        $users = User::with('organizations')->latest()->paginate(15);
        return view('authentication::users.index', compact('users'));
    }

    public function data()
    {
        $this->authorizeAdmin();

        $query = User::select(['id','name','email','role','is_active','created_at'])->latest();

        return DataTables::eloquent($query)
            ->addColumn('role_badge', function ($u) {
                $map = ['admin' => 'bg-red-100 text-red-700 border-red-200', 'manager' => 'bg-blue-100 text-blue-700 border-blue-200'];
                $cls = $map[$u->role] ?? 'bg-gray-100 text-gray-600 border-gray-200';
                return '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border '.$cls.'">'.ucfirst($u->role ?? 'viewer').'</span>';
            })
            ->addColumn('status_badge', fn ($u) => $u->is_active
                ? '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-emerald-100 text-emerald-700 border-emerald-200">Active</span>'
                : '<span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border bg-red-100 text-red-600 border-red-200">Inactive</span>')
            ->addColumn('actions', function ($u) {
                $edit = '<a href="'.route('users.edit', $u).'" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 transition-colors">Edit</a>';
                $del  = '<form action="'.route('users.destroy', $u).'" method="POST" class="inline" onsubmit="return confirm(\'Delete this user?\')">'.csrf_field().method_field('DELETE').'<button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors">Delete</button></form>';
                return '<div class="flex items-center gap-1.5">'.$edit.$del.'</div>';
            })
            ->rawColumns(['role_badge', 'status_badge', 'actions'])
            ->toJson();
    }

    public function create()
    {
        $this->authorizeAdmin();
        $organizations = Organization::where('is_active', true)->get();
        return view('authentication::users.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'          => ['required', 'confirmed', Password::defaults()],
            'role'              => ['required', 'in:admin,manager,viewer'],
            'organizations'     => ['nullable', 'array'],
            'organizations.*'   => ['exists:organizations,id'],
            'org_role'          => ['nullable', 'in:admin,manager,viewer'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => $data['role'],
        ]);

        if (!empty($data['organizations'])) {
            $orgRole = $data['org_role'] ?? 'viewer';
            $syncData = array_fill_keys($data['organizations'], ['role' => $orgRole]);
            $user->organizations()->sync($syncData);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $this->authorizeAdmin();
        $organizations = Organization::where('is_active', true)->get();
        $userOrgIds = $user->organizations->pluck('id')->toArray();
        return view('authentication::users.edit', compact('user', 'organizations', 'userOrgIds'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeAdmin();
        $data = $request->validate([
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password'          => ['nullable', 'confirmed', Password::defaults()],
            'role'              => ['required', 'in:admin,manager,viewer'],
            'is_active'         => ['boolean'],
            'organizations'     => ['nullable', 'array'],
            'organizations.*'   => ['exists:organizations,id'],
            'org_role'          => ['nullable', 'in:admin,manager,viewer'],
        ]);

        $updateData = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $request->boolean('is_active'),
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        $orgRole = $data['org_role'] ?? 'viewer';
        $syncData = [];
        foreach ($data['organizations'] ?? [] as $orgId) {
            $syncData[$orgId] = ['role' => $orgRole];
        }
        $user->organizations()->sync($syncData);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorizeAdmin();
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted.');
    }

    public function profile()
    {
        return view('authentication::users.profile', ['user' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $updateData = [
            'name'  => $data['name'],
            'email' => $data['email'],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        return back()->with('success', 'Profile updated.');
    }
}
