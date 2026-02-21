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

        Chapter::firstOrCreate([
            'course_id' => $course->id,
            'name' => 'Persiapan',
        ]);

        Chapter::firstOrCreate([
            'course_id' => $course->id,
            'name' => 'Latihan',
        ]);

        Chapter::firstOrCreate([
            'course_id' => $course->id,
            'name' => 'Mastering',
        ]);
    }
}
