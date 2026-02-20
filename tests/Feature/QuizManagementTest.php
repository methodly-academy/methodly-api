<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Quiz;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $chapter;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->student = User::factory()->create();
        $this->student->assignRole('student');

        $level = Level::create(['name' => 'Beginner', 'slug' => 'beginner']);
        $course = Course::create(['level_id' => $level->id, 'name' => 'Laravel Basic', 'slug' => 'laravel-basic']);
        $this->chapter = Chapter::create(['course_id' => $course->id, 'name' => 'Introduction']);
    }

    /** @test */
    public function admin_can_create_quiz()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/quizzes', [
                'chapter_id' => $this->chapter->id,
                'title' => 'Laravel Logic Test'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quizzes', ['title' => 'Laravel Logic Test']);
    }

    /** @test */
    public function admin_can_list_quizzes_with_optional_filters()
    {
        Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'Quiz 1']);
        
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/quizzes?chapter_id={$this->chapter->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function admin_can_show_quiz_detail()
    {
        $quiz = Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'Detailed Quiz']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/quizzes/{$quiz->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', 'Detailed Quiz');
    }

    /** @test */
    public function admin_can_update_quiz()
    {
        $quiz = Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'Old Title']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/quizzes/{$quiz->id}", [
                'title' => 'New Awesome Title'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('quizzes', ['id' => $quiz->id, 'title' => 'New Awesome Title']);
    }

    /** @test */
    public function admin_can_delete_quiz()
    {
        $quiz = Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'To Be Deleted']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/quizzes/{$quiz->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
    }

    /** @test */
    public function student_cannot_access_admin_quiz_endpoints()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/v1/admin/quizzes', [
                'chapter_id' => $this->chapter->id,
                'title' => 'Illegal Quiz'
            ]);

        $response->assertStatus(403);
    }
}
