<?php

namespace App\Services\Admin;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    /**
     * Get all roles with their permissions count.
     */
    public function getAllRoles(): Collection
    {
        return Role::withCount('permissions')->withCount('users')->get();
    }

    /**
     * Get a single role with its permissions.
     */
    public function getRole(int $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Get all available permissions grouped by a logical module if possible.
     * Currently we just return all of them.
     */
    public function getAllPermissions(): Collection
    {
        return Permission::orderBy('name')->get();
    }

    /**
     * Update a role's permissions.
     */
    public function updateRolePermissions(Role $role, array $permissions): void
    {
        // To prevent locking out the admin, ensure the 'admin' role always has all permissions or at least critical ones.
        if ($role->name === 'admin') {
            $role->syncPermissions(Permission::all());
            return;
        }

        $role->syncPermissions($permissions);
    }
}
