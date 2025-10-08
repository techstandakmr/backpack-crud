<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;

class LessonFactory extends Factory
{
    protected $model = \App\Models\Lesson::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(),
            'course_id' => Course::inRandomOrder()->first()->id,
        ];
    }
}
