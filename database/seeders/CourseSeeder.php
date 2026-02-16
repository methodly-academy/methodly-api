<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Course::create([
            'name' => 'Kelas A',
            'slug' => Str::slug('Kelas A','-'),
            'type' => 'free',
            'price' => 0,
            'is_published' => true,
            'level_id' => 1,
        ]);

        Course::create([
            'name' => 'Kelas B',
            'slug' => Str::slug('Kelas B','-'),
            'type' => 'premium',
            'price' => 200000,
            'is_published' => false,
            'level_id' => 2,
        ]);

        Course::create([
            'name' => 'Basic UI/UX using Figma',
            'slug' => Str::slug('Basic UI/UX using Figma','-'),
            'type' => 'premium',
            'price' => '150000',
            'is_published' => true,
            'level_id' => 1,
        ]);
    }
}
