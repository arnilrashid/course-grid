<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RoleService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
    }

    public function index()
    {
        return Inertia::render('Admin/Roles/Index', [
            'roles' => $this->roleService->getAllRoles(),
        ]);
    }

    public function edit(Role $role)
    {
        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role->load('permissions'),
            'permissions' => $this->roleService->getAllPermissions(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $this->roleService->updateRolePermissions($role, $validated['permissions'] ?? []);

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
