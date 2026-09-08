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
     * Update a role's name and permissions.
     * The admin role name is protected and cannot be changed.
     */
    public function updateRole(Role $role, array $data): void
    {
        if ($role->name !== 'admin' && isset($data['name'])) {
            $role->update(['name' => $data['name']]);
        }

        $this->updateRolePermissions($role, $data['permissions'] ?? []);
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

    /**
     * Create a new role with permissions.
     */
    public function createRole(string $name, array $permissions): Role
    {
        $role = Role::create(['name' => $name]);
        $role->syncPermissions($permissions);
        return $role;
    }

    /**
     * Delete a role.
     */
    public function deleteRole(Role $role): void
    {
        if ($role->name === 'admin') {
            throw new \Exception("Cannot delete the admin role.");
        }
        $role->delete();
    }
}
