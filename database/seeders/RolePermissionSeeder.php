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
            $createdPermissions[$permission] = Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // --- Role Student ---
        $studentRole = Role::create(['name' => 'student', 'guard_name' => 'web']);
        $studentRole->givePermissionTo([
            $createdPermissions['enroll in course'], 
            $createdPermissions['track progress'], 
            $createdPermissions['view dashboard']
        ]);

        // --- Role Admin ---
        $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        // Admin mendapatkan semua permission
        $adminRole->givePermissionTo(Permission::all());

        // Default Admin
        $adminUser = User::factory()->create([
            'name' => 'Admin Methodly',
            'email' => 'admin@methodly.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole($adminRole);

        // Default Student
        $studentUser = User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@methodly.com',
            'password' => Hash::make('password'),
        ]);
        $studentUser->assignRole($studentRole);
    }
}
