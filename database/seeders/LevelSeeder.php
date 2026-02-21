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
        Level::firstOrCreate([
            'slug' => Str::slug('beginner')
        ], [
            'name' => 'beginner',
        ]);

        Level::firstOrCreate([
            'slug' => Str::slug('expert')
        ], [
            'name' => 'expert',
        ]); 
    }
}
