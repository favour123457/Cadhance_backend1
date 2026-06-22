<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\PermissionRole;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->latest()->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.add', compact('permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
        ]);

        $role = Role::create(['name' => $request->name]);

        if ($request->has('permissions')) {
            foreach ($request->permissions as $permission_id) {
                PermissionRole::create([
                    'role_id'       => $role->id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        flash()->success('Role created successfully.');
        return redirect()->route('roles.index');
    }

    public function edit(Role $role)
    {
        $permissions        = Permission::all();
        $rolePermissionIds  = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissionIds'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        PermissionRole::where('role_id', $role->id)->delete();
        if ($request->has('permissions')) {
            foreach ($request->permissions as $permission_id) {
                PermissionRole::create([
                    'role_id'       => $role->id,
                    'permission_id' => $permission_id,
                ]);
            }
        }

        flash()->success('Role updated successfully.');
        return redirect()->route('roles.index');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        flash()->success('Role deleted successfully.');
        return redirect()->route('roles.index');
    }
}
