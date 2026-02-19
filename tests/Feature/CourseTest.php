<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $levelBeginner;
    protected $levelExpert;

    protected function setUp(): void{
        parent::setUp();

        // Inisialisasi role admin
        \Spatie\Permission\Models\Role::create(['name' => 'admin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'student', 'guard_name' => 'web']);
        
        // Buat user admin
        $this->admin = User::factory()->create();
        $this->student = User::factory()->create();

        // Buat Level
        $this->levelBeginner = Level::create([
            'name' => 'beginner',
            'slug' => Str::slug('beginner')
        ]);

        $this->levelExpert = Level::create([
            'name' => 'expert',
            'slug' => Str::slug('expert')
        ]);
    }

    public function test_admin_create_course_successfully(): void
    {

        $payload = [
            'name' => 'Kelas Laravel Profesional',
            'slug' => 'kelas-laravel-profesional',
            'description' => 'Belajar backend sampai mahir.',
            'type' => 'premium',
            'price' => 500000,
            'level_id' => $this->levelExpert->id,
            'is_published' => true,
        ];

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->postJson('/api/v1/admin/courses', $payload);

        $response->assertStatus(201)->assertJson([
            'status' => 'success',
            'message' => 'course created successfully',
            'data' => [
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]
        ]);

        $this->assertDatabaseHas('courses',[
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_admin_create_course_failed_with_empty_data(): void 
    {
        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->postJson('/api/v1/admin/courses', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['name','type', 'level_id']);
    }

    public function test_student_create_course_failed(): void
    {

        $payload = [
            'name' => 'Kelas Dasar Codeigniter',
            'slug' => 'kelas-dasar-codeigniter',
            'description' => 'Belajar backend dari dasar sampai mahir.',
            'type' => 'premium',
            'price' => 75000,
            'level_id' => $this->levelExpert->id,
            'is_published' => true,
        ];

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->postJson('/api/v1/admin/courses', $payload);

        $response->assertStatus(403);
    }

    public function test_guest_create_course_failed(): void
    {

        $payload = [
            'name' => 'Kelas Dasar Bootstrap',
            'slug' => 'kelas-dasar-bootstrap',
            'description' => 'Belajar layout frontend dari dasar menggunakan CSS Framework Bootstrap 5.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelBeginner->id,
            'is_published' => true,
        ];

        $response = $this->postJson('/api/v1/admin/courses', $payload);

        $response->assertStatus(401);
    }

    public function test_admin_update_course_successfully(): void
    {

        $course = Course::create([
            'name' => 'Kelas Git',
            'slug' => 'kelas-git',
            'description' => 'Belajar kolaborasi menggunakan.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelExpert->id,
            'is_published' => false,
        ]);
        
        $payload = [
            'name' => 'Kelas Dasar Git',
            'slug' => 'kelas-dasar-git',
            'description' => 'Belajar kolaborasi menggunakan Git dan GitHub.',
            'type' => 'premium',
            'price' => 125000,
            'level_id' => $this->levelExpert->id,
            'is_published' => true,
        ];

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->putJson("/api/v1/admin/courses/{$course->id}", $payload);

        $response->assertStatus(200)->assertJson([
            'status' => 'success',
            'message' => 'course updated successfully',
            'data' => [
                'name' => $payload['name'],
                'slug' => $payload['slug'],
            ]
        ]);

        $this->assertDatabaseHas('courses',[
            'name' => $payload['name'],
            'slug' => $payload['slug'],
        ]);
    }

    public function test_student_update_course_failed(): void
    {

        $course = Course::create([
            'name' => 'Kelas Bulma',
            'slug' => 'kelas-bulma',
            'description' => 'Belajar menghias tampilan website menggunakan.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelBeginner->id,
            'is_published' => false,
        ]);
        
        $payload = [
            'name' => 'Kelas Dasar Git',
            'slug' => 'kelas-dasar-git',
            'description' => 'Belajar menghias tampilan website menggunakan CSS Framework Bulma.',
            'type' => 'premium',
            'price' => 15000,
            'level_id' => $this->levelBeginner->id,
            'is_published' => true,
        ];

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->putJson("/api/v1/admin/courses/{$course->id}", $payload);

        $response->assertStatus(403);
    }

    public function test_guest_update_course_failed(): void 
    {

 
        $course = Course::create([
            'name' => 'Kelas Kit',
            'slug' => 'kelas-kit',
            'description' => 'Belajar memoles motor menggunakan.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelExpert->id,
            'is_published' => false,
        ]);
        
        $payload = [
            'name' => 'Kelas Dasar Git',
            'slug' => 'kelas-dasar-git',
            'description' => 'Belajar memoles motor menggunakan Kit.',
            'type' => 'premium',
            'price' => 12000,
            'level_id' => $this->levelBeginner->id,
            'is_published' => true,
        ];

        $response = $this->putJson("/api/v1/admin/courses/{$course->id}", $payload);

        $response->assertStatus(401);
    }

    public function test_admin_delete_course_successfully(): void 
    {

        $course = Course::create([
            'name' => 'Metode Penelitian',
            'slug' => 'metode-penelitian',
            'description' => 'Belajar melakukan penelitian menggunakan metode.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelBeginner->id,
            'is_published' => false,
        ]);

        $this->admin->assignRole('admin');
        Sanctum::actingAs($this->admin);
        $response = $this->deleteJson("/api/v1/admin/courses/{$course->id}");

        $response->assertStatus(200)->assertJson([
            'status' => 'success',
            'message' => 'course deleted successfully',
        ]);
    }

    public function test_student_delete_course_failed(): void
    {
        
        $course = Course::create([
            'name' => 'Kelas ABC',
            'slug' => 'kelas-abc',
            'description' => 'Belajar melakukan penelitian menggunakan metode ABC.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelBeginner->id,
            'is_published' => true,
        ]);

        $this->student->assignRole('student');
        Sanctum::actingAs($this->student);
        $response = $this->deleteJson("/api/v1/admin/courses/{$course->id}");

        $response->assertStatus(403);
    }

    public function test_guest_delete_course_failed(): void
    {
        
        $course = Course::create([
            'name' => 'Kelas XYZ',
            'slug' => 'kelas-xyz',
            'description' => 'Belajar melakukan penelitian menggunakan metode xyz.',
            'type' => 'free',
            'price' => 0,
            'level_id' => $this->levelBeginner->id,
            'is_published' => true,
        ]);

        $response = $this->deleteJson("/api/v1/admin/courses/{$course->id}");

        $response->assertStatus(401);
    }
}
