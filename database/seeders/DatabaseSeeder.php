<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        // 1. Admin
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@coursegrid.test',
        ]);
        $admin->assignRole('admin');

        // 2. Student
        $student = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@coursegrid.test',
        ]);
        $student->assignRole('student');

        // 3. Instructor
        $instructor = User::factory()->create([
            'name' => 'Instructor User',
            'email' => 'instructor@coursegrid.test',
        ]);
        $instructor->assignRole('instructor');

        // 4. Support Manager
        $support = User::factory()->create([
            'name' => 'Support Manager',
            'email' => 'support@coursegrid.test',
        ]);
        $support->assignRole('support');
    }
}
