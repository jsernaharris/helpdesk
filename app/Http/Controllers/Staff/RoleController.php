<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Core roles referenced by name in User helpers (isMspAdmin, etc.).
     * Their permissions may be edited, but they cannot be renamed or deleted.
     */
    private const PROTECTED_ROLES = ['msp_admin', 'msp_technician', 'customer_admin', 'customer_user'];

    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('staff.roles.index', [
            'roles' => $roles,
            'protected' => self::PROTECTED_ROLES,
        ]);
    }

    public function create()
    {
        return view('staff.roles.create', [
            'permissionGroups' => $this->permissionGroups(),
            'assigned' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')],
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ], [
            'name.regex' => 'Use lowercase letters, numbers, and underscores only (e.g. site_a_tech).',
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('staff.roles.index')
            ->with('success', "Role \"{$role->name}\" created.");
    }

    public function edit(Role $role)
    {
        return view('staff.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'assigned' => $role->permissions->pluck('name')->all(),
            'isProtected' => in_array($role->name, self::PROTECTED_ROLES),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $isProtected = in_array($role->name, self::PROTECTED_ROLES);

        $rules = [
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ];
        if (! $isProtected) {
            $rules['name'] = ['required', 'string', 'max:255', 'regex:/^[a-z0-9_]+$/', Rule::unique('roles', 'name')->ignore($role->id)];
        }

        $data = $request->validate($rules, [
            'name.regex' => 'Use lowercase letters, numbers, and underscores only (e.g. site_a_tech).',
        ]);

        if (! $isProtected) {
            $role->update(['name' => $data['name']]);
        }
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('staff.roles.index')
            ->with('success', "Role \"{$role->name}\" updated.");
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, self::PROTECTED_ROLES)) {
            return back()->with('error', "The \"{$role->name}\" role is built in and cannot be deleted.");
        }

        $name = $role->name;
        $role->delete(); // spatie cascades the role_has_permissions / model_has_roles pivots

        return redirect()->route('staff.roles.index')
            ->with('success', "Role \"{$name}\" deleted.");
    }

    /**
     * All permissions grouped by their dotted prefix for display
     * (e.g. "tickets" => ['tickets.view_all', ...]).
     */
    private function permissionGroups(): array
    {
        return Permission::orderBy('name')->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->map(fn ($group) => $group->pluck('name')->all())
            ->all();
    }
}
