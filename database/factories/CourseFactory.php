<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Daftar topik agar tidak random aneh
        $topics = ['Laravel', 'React', 'Vue', 'Python', 'Docker', 'AWS', 'Tailwind', 'NodeJS', 'Golang', 'Git'];
        $name = $this->faker->randomElement($topics) . ' ' . $this->faker->jobTitle;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $this->faker->randomElement(['free', 'premium']),
            'price' => function (array $attributes) {
                return $attributes['type'] === 'free' ? 0 : fake()->numberBetween(100000, 500000);
            },
            'is_published' => $this->faker->boolean(80), // 80% kemungkinan true
            // Angka dapat berubah-ubah selama proses development
            'level_id' => $this->faker->numberBetween(1, 2),
        ];
    }
}
