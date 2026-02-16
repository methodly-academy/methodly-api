<?php

namespace Database\Seeders;

use App\Models\Level;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Level::create([
            'name' => 'beginner',
            'slug' => Str::slug('beginner')
        ]);

        Level::create([
            'name' => 'expert',
            'slug' => Str::slug('expert')
         ]); 
    }
}
