<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['organization', 'roles']);

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }
        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->orderBy('name')->paginate(25)->withQueryString();
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('staff.users.index', compact('users', 'organizations', 'roles'));
    }

    public function create()
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('staff.users.create', compact('organizations', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organization_id' => $request->organization_id,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('staff.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $user->load(['organization', 'roles', 'teams']);
        $recentTickets = $user->assignedTickets()
            ->with('organization')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('staff.users.show', compact('user', 'recentTickets'));
    }

    public function edit(User $user)
    {
        $organizations = Organization::where('is_active', true)->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('staff.users.edit', compact('user', 'organizations', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'organization_id' => 'required|exists:organizations,id',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:50',
            'job_title' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'organization_id' => $request->organization_id,
            'phone' => $request->phone,
            'job_title' => $request->job_title,
            'is_active' => $request->boolean('is_active', true),
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        $user->syncRoles([$request->role]);

        return redirect()->route('staff.users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('staff.users.index')
            ->with('success', 'User deleted.');
    }
}
