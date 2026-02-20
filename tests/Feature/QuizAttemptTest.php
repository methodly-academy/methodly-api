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

class QuizAttemptTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $quiz;
    protected $question;
    protected $option;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->student = User::factory()->create();
        $this->student->assignRole('student');

        $level = Level::create(['name' => 'Expert', 'slug' => 'expert']);
        $course = Course::create(['level_id' => $level->id, 'name' => 'Laravel Advanced', 'slug' => 'laravel-advanced']);
        $chapter = Chapter::create(['course_id' => $course->id, 'name' => 'Deep Dive']);
        $this->quiz = Quiz::create(['chapter_id' => $chapter->id, 'title' => 'Final Exam']);
        
        $this->question = QuizQuestion::create([
            'quiz_id' => $this->quiz->id,
            'question_text' => 'What is 2+2?',
            'question_type' => 'multiple_choice',
            'points' => 10
        ]);

        $this->option = QuizOption::create([
            'quiz_question_id' => $this->question->id,
            'option_text' => '4',
            'is_correct' => true
        ]);
        
        QuizOption::create([
            'quiz_question_id' => $this->question->id,
            'option_text' => '5',
            'is_correct' => false
        ]);
    }

    /** @test */
    public function student_can_start_quiz()
    {
        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/quizzes/{$this->quiz->id}/start");

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'status' => 'started'
        ]);
    }

    /** @test */
    public function student_can_submit_answer()
    {
        $attempt = QuizAttempt::create([
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'status' => 'started',
            'started_at' => now()
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/attempts/{$attempt->id}/answer", [
                'question_id' => $this->question->id,
                'option_id' => $this->option->id
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('quiz_attempt_answers', [
            'quiz_attempt_id' => $attempt->id,
            'quiz_question_id' => $this->question->id,
            'is_correct' => true
        ]);
    }

    /** @test */
    public function student_can_finish_quiz()
    {
        $attempt = QuizAttempt::create([
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'status' => 'started',
            'started_at' => now()
        ]);

        // Submit jawaban
        $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/attempts/{$attempt->id}/answer", [
                'question_id' => $this->question->id,
                'option_id' => $this->option->id
            ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->postJson("/api/v1/attempts/{$attempt->id}/finish");

        $response->assertStatus(200);
        $response->assertJsonPath('data.final_score', 10);
        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
            'score' => 10
        ]);
    }

    /** @test */
    public function student_can_see_their_attempt_history()
    {
        QuizAttempt::create([
            'user_id' => $this->student->id,
            'quiz_id' => $this->quiz->id,
            'status' => 'completed',
            'score' => 10,
            'started_at' => now(),
            'completed_at' => now()
        ]);

        $response = $this->actingAs($this->student, 'sanctum')
            ->getJson('/api/v1/my-attempts');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
    }
}
