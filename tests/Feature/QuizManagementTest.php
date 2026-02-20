<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use App\Models\QuizAttempt;
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
    public function student_cannot_create_quiz()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson('/api/v1/admin/quizzes', [
                'chapter_id' => $this->chapter->id,
                'title' => 'Illegal Quiz'
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_question_with_options()
    {
        $quiz = Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'General Quiz']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/admin/quiz-questions', [
                'quiz_id' => $quiz->id,
                'question_text' => 'What is 1+1?',
                'question_type' => 'multiple_choice',
                'points' => 10,
                'options' => [
                    ['option_text' => '2', 'is_correct' => true],
                    ['option_text' => '3', 'is_correct' => false],
                ]
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quiz_questions', ['question_text' => 'What is 1+1?']);
        $this->assertDatabaseHas('quiz_options', ['option_text' => '2', 'is_correct' => true]);

        // Verify index works with quiz_id filter
        $indexResponse = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/admin/quiz-questions?quiz_id={$quiz->id}");
        
        $indexResponse->assertStatus(200);
        $indexResponse->assertJsonCount(1, 'data');
    }

    /** @test */
    public function student_can_perform_full_quiz_flow()
    {
        // 1. Setup Data
        $quiz = Quiz::create(['chapter_id' => $this->chapter->id, 'title' => 'Student Test']);
        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Is Laravel a PHP framework?',
            'question_type' => 'boolean',
            'points' => 20
        ]);
        $optTrue = QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => 'Yes', 'is_correct' => true]);
        $optFalse = QuizOption::create(['quiz_question_id' => $question->id, 'option_text' => 'No', 'is_correct' => false]);

        // 2. Start Quiz
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/quizzes/{$quiz->id}/start");
        
        $response->assertStatus(201);
        $attemptId = $response->json('data.id');

        // 3. Submit Answer
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/attempts/{$attemptId}/answer", [
                'question_id' => $question->id,
                'option_id' => $optTrue->id
            ]);
        
        $response->assertStatus(200);
        $this->assertDatabaseHas('quiz_attempt_answers', [
            'quiz_attempt_id' => $attemptId,
            'is_correct' => true,
            'points_earned' => 20
        ]);

        // 4. Finish Quiz
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/attempts/{$attemptId}/finish");
        
        $response->assertStatus(200);
        $response->assertJsonPath('data.final_score', 20);
        
        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attemptId,
            'status' => 'completed',
            'score' => 20
        ]);
    }
}
