<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar Permission
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage permissions',
            'enroll in course',
            'track progress',
        ];

        $createdPermissions = [];
        foreach ($permissions as $permission) {
            $createdPermissions[$permission] = Permission::findOrCreate($permission, 'web');
        }

        // --- Role Student ---
        $studentRole = Role::findOrCreate('student', 'web');
        $studentRole->givePermissionTo([
            $createdPermissions['enroll in course'], 
            $createdPermissions['track progress'], 
            $createdPermissions['view dashboard']
        ]);

        // --- Role Admin ---
        $adminRole = Role::findOrCreate('admin', 'web');
        // Admin mendapatkan semua permission
        $adminRole->givePermissionTo(Permission::all());

        // Default Admin
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@methodly.com'],
            [
                'name' => 'Admin Methodly',
                'password' => Hash::make('password'),
            ]
        );
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        // Default Student
        $studentUser = User::updateOrCreate(
            ['email' => 'student@methodly.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$studentUser->hasRole('student')) {
            $studentUser->assignRole($studentRole);
        }
    }
}
