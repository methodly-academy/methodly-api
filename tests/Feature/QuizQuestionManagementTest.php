<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizQuestionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $student;
    protected $quiz;

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
        $chapter = Chapter::create(['course_id' => $course->id, 'name' => 'Introduction']);
        $this->quiz = Quiz::create(['chapter_id' => $chapter->id, 'title' => 'Sample Quiz']);
    }

    /** @test */
    public function admin_can_create_question_with_options()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/quiz-questions', [
                'quiz_id' => $this->quiz->id,
                'question_text' => 'What is Laravel?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'explanation' => 'A PHP Framework',
                'options' => [
                    ['option_text' => 'Framework', 'is_correct' => true],
                    ['option_text' => 'Library', 'is_correct' => false],
                ]
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quiz_questions', ['question_text' => 'What is Laravel?']);
        $this->assertDatabaseHas('quiz_options', ['option_text' => 'Framework', 'is_correct' => true]);
    }

    /** @test */
    public function admin_can_list_questions_by_quiz_id()
    {
        QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Q1',
            'question_type' => 'short_answer',
            'points' => 10
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/quiz-questions?quiz_id={$this->quiz->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }

    /** @test */
    public function admin_can_show_question_detail()
    {
        $question = QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Detail Test',
            'question_type' => 'boolean',
            'points' => 5
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/quiz-questions/{$question->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.question_text', 'Detail Test');
    }

    /** @test */
    public function admin_can_update_question_and_options()
    {
        $question = QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Old Question',
            'question_type' => 'boolean',
            'points' => 5
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->putJson("/api/v1/admin/quiz-questions/{$question->id}", [
                'question_text' => 'Updated Question',
                'options' => [
                    ['option_text' => 'New Option', 'is_correct' => true]
                ]
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('quiz_questions', ['id' => $question->id, 'question_text' => 'Updated Question']);
        $this->assertDatabaseHas('quiz_options', ['quiz_question_id' => $question->id, 'option_text' => 'New Option']);
    }

    /** @test */
    public function admin_can_delete_question()
    {
        $question = QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'Delete Me',
            'question_type' => 'essay',
            'points' => 10
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->deleteJson("/api/v1/admin/quiz-questions/{$question->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('quiz_questions', ['id' => $question->id]);
    }

    /** @test */
    public function student_cannot_access_admin_question_endpoints()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/v1/admin/quiz-questions', [
                'quiz_id' => $this->quiz->id,
                'question_text' => 'Illegal Question',
                'question_type' => 'essay'
            ]);

        $response->assertStatus(403);
    }
}
