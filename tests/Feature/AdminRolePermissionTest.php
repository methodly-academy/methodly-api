<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    /**
     * Set up awal sebelum setiap test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Inisialisasi role admin
        $role = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        
        // Buat user admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    /**
     * Test admin dapat melihat daftar role.
     */
    public function test_admin_can_list_roles()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/admin/roles');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'admin']);
    }

    /**
     * Test admin dapat membuat role baru.
     */
    public function test_admin_can_create_role()
    {
        $payload = ['name' => 'editor'];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/roles', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Role berhasil dibuat']);

        $this->assertDatabaseHas('roles', ['name' => 'editor']);
    }

    /**
     * Test admin dapat membuat permission baru.
     */
    public function test_admin_can_create_permission()
    {
        $payload = ['name' => 'delete posts'];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/permissions', $payload);

        $response->assertStatus(201)
            ->assertJson(['message' => 'Izin berhasil dibuat']);

        $this->assertDatabaseHas('permissions', ['name' => 'delete posts']);
    }

    /**
     * Test admin dapat memberikan izin ke role (sync).
     */
    public function test_admin_can_sync_permissions_to_role()
    {
        $role = Role::create(['name' => 'manager']);
        $permission1 = Permission::create(['name' => 'view reports']);
        $permission2 = Permission::create(['name' => 'edit settings']);

        $payload = [
            'permissions' => ['view reports', 'edit settings']
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/roles/{$role->id}/permissions", $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Izin berhasil disinkronisasi ke role']);

        $this->assertTrue($role->hasPermissionTo('view reports'));
        $this->assertTrue($role->hasPermissionTo('edit settings'));
    }

    /**
     * Test admin dapat mengubah role user.
     */
    public function test_admin_can_sync_user_roles()
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'instructor']);

        $payload = [
            'roles' => ['instructor']
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson("/api/v1/admin/users/{$user->id}/roles", $payload);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role user berhasil diperbarui']);

        $this->assertTrue($user->hasRole('instructor'));
    }

    /**
     * Test user biasa tidak bisa mengakses menu admin (Forbidden).
     */
    public function test_regular_user_cannot_access_admin_routes()
    {
        $user = User::factory()->create();
        // Berikan role student yang tidak punya akses admin
        $studentRole = Role::create(['name' => 'student']);
        $user->assignRole($studentRole);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/admin/roles');

        $response->assertStatus(403);
    }
}
