<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Chapter;
use Illuminate\Database\Seeder;

class ChapterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $course = Course::first();

        if (!$course) {
            return;
        }

        Chapter::create([
            'course_id' => $course->id,
            'name' => 'Persiapan',
        ]);

        Chapter::create([
            'course_id' => $course->id,
            'name' => 'Latihan',
        ]);

        Chapter::create([
            'course_id' => $course->id,
            'name' => 'Mastering',
        ]);
    }
}
