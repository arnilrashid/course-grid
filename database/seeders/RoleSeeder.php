<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Seed roles and permissions.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // Course management (instructor)
            'course.create',
            'course.edit',
            'course.publish',
            'course.delete',
            'quiz.manage',
            'coupon.create',      // instructor: their own coupons
            'bundle.manage',

            // Taxonomy (admin only)
            'category.manage',

            // Refunds / support
            'refund.review',
            'ticket.manage',
            'activity.view',

            // Platform-wide (admin only)
            'coupon.manage-all',  // platform-wide coupons
            'translation.manage',
            'settings.manage',
            'user.manage',
            'instructor-application.review',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Admin — everything
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // Instructor — course + their own commerce tools
        $instructor = Role::firstOrCreate(['name' => 'instructor']);
        $instructor->syncPermissions([
            'course.create',
            'course.edit',
            'course.publish',
            'course.delete',
            'quiz.manage',
            'coupon.create',
            'bundle.manage',
        ]);

        // Support manager — read/monitor + refund/ticket handling, no course editing
        $support = Role::firstOrCreate(['name' => 'support']);
        $support->syncPermissions([
            'refund.review',
            'ticket.manage',
            'activity.view',
        ]);

        // Student — no special permissions
        // (enrollment/access checks are done via "is this user enrolled?" not a permission)
        Role::firstOrCreate(['name' => 'student']);
    }
}
