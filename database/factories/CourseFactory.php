<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\User;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $teacher = User::where('role', 'teacher')->inRandomOrder()->first();

        // Fallback if no teacher exists yet
        if (!$teacher) {
            $teacher = User::factory()->create(['role' => 'teacher']);
        }

        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'author_id' => $teacher->id,
        ];
    }
}
