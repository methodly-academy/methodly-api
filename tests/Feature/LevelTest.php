<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Level;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LevelTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;

    protected function setUp(): void{
        parent::setUp();

        // Inisialisasi role admin
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'student', 'guard_name' => 'web']);
        
        // Buat user admin
        $this->admin = User::factory()->create();
        $this->student = User::factory()->create();
    }

    public function test_admin_get_levels_successfully(): void
    {
        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->getJson('/api/v1/admin/levels');

        $response->assertStatus(200)->assertJson([
            'status' => 'success',
            'message' => 'list of all levels',
        ]);
    }

    public function test_student_get_levels_successfully(): void{
        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->getJson('/api/v1/admin/levels');

        $response->assertStatus(403);

    }

    public function test_guest_get_levels_failed(): void{
        $response = $this->getJson('/api/v1/admin/levels');

        $response->assertStatus(401);
    }

    public function test_admin_create_level_successfully(): void{
        $payload = [
            'name' => 'easy hard',
            'slug' => 'easy-hard',
        ];

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->postJson('/api/v1/admin/levels', $payload);

        $response->assertStatus(201)->assertJson([
            'status' => 'success',
            'message' => 'level created successfully',
            'data' => [
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]
        ]);

        $this->assertDatabaseHas('levels',[
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_student_create_level_failed(): void{
        $payload = [
            'name' => 'super easy hard',
            'slug' => 'super-easy-hard',
        ];

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->postJson('/api/v1/admin/levels', $payload);

        $response->assertStatus(403);
    }

    public function test_guest_create_level_failed(): void{
        $payload = [
            'name' => 'super medium hard',
            'slug' => 'super-medium-hard',
        ];

        $response = $this->postJson('/api/v1/admin/levels', $payload);

        $response->assertStatus(401);
    }

    public function test_admin_update_level_successfully(): void{
        $level = Level::create([
            'name' => 'hard easy',
            'slug' => 'hard-easy',
        ]);

        // Data untuk update
        $payload = [
            'name' => 'medium medium',
            'slug' => 'medium-medium',
        ];

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->putJson("/api/v1/admin/levels/{$level->id}",$payload)->assertStatus(200)->assertJson([
            'message' => 'level updated successfully',
            'data' => [
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]
        ]);

        $this->assertDatabaseHas('levels',[
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_student_update_level_failed(): void{
        $level = Level::create([
            'name' => 'beginner hard',
            'slug' => 'beginner-hard',
        ]);

        // Data untuk update
        $payload = [
            'name' => 'hard medium',
            'slug' => 'hard-medium',
        ];

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->putJson("/api/v1/admin/levels/{$level->id}", $payload)->assertStatus(403);
    }

    public function test_guest_update_level_failed(): void{
        $level = Level::create([
            'name' => 'easy beginner',
            'slug' => 'easy-beginner',
        ]);

        // Data untuk update
        $payload = [
            'name' => 'hard expert',
            'slug' => 'hard-expert',
        ];

        $response = $this->putJson("/api/v1/admin/levels/{$level->id}", $payload)->assertStatus(401);
    }

    public function test_admin_delete_level_successfully(): void{
        $level = Level::create([
            'name' => 'easy beginner',
            'slug' => 'easy-beginner',
        ]);

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->deleteJson("/api/v1/admin/levels/{$level->id}")->assertStatus(200)->assertJson([
            'message' => 'level deleted successfully',
        ]);
    }

    public function test_student_delete_level_failed(): void{
        $level = Level::create([
            'name' => 'test expert',
            'slug' => 'test-expert',
        ]);

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->deleteJson("/api/v1/admin/levels/{$level->id}")->assertStatus(403);
    }

    public function test_guest_delete_level_failed(): void{
        $level = Level::create([
            'name' => 'easy expert',
            'slug' => 'easy-expert',
        ]);

        $response = $this->deleteJson("/api/v1/admin/levels/{$level->id}")->assertStatus(401);
    }
}
