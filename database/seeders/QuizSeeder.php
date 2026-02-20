<?php

namespace Database\Seeders;

use App\Models\Chapter;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizOption;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $chapter = Chapter::first();

        if (!$chapter) {
            return;
        }

        // 1. Create a Quiz
        $quiz = Quiz::create([
            'chapter_id' => $chapter->id,
            'title' => 'Fundamental Laravel Quiz',
        ]);

        // 2. Create Multiple Choice Question
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Apa command untuk membuat controller di Laravel?',
            'question_type' => 'multiple_choice',
            'points' => 10,
            'explanation' => 'php artisan make:controller adalah command standar Laravel.'
        ]);

        QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'php artisan make:controller', 'is_correct' => true]);
        QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'php artisan generate:controller', 'is_correct' => false]);
        QuizOption::create(['quiz_question_id' => $q1->id, 'option_text' => 'php artisan create:controller', 'is_correct' => false]);

        // 3. Create Boolean Question
        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Laravel adalah framework berbasis Javascript.',
            'question_type' => 'boolean',
            'points' => 5,
            'explanation' => 'Salah, Laravel adalah framework berbasis PHP.'
        ]);

        QuizOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'True', 'is_correct' => false]);
        QuizOption::create(['quiz_question_id' => $q2->id, 'option_text' => 'False', 'is_correct' => true]);

        // 4. Create Short Answer Question
        $q3 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Siapa pencipta Laravel?',
            'question_type' => 'short_answer',
            'points' => 15,
            'explanation' => 'Taylor Otwell adalah pencipta Laravel.'
        ]);

        // 5. Create Essay Question
        $q4 = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Jelaskan apa itu Eloquent ORM menurut pemahaman Anda!',
            'question_type' => 'essay',
            'points' => 20,
            'explanation' => 'Eloquent adalah ORM bawaan Laravel yang memudahkan interaksi dengan database menggunakan model.'
        ]);
    }
}
