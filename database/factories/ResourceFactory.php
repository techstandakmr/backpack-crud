<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Lesson;

class ResourceFactory extends Factory
{
    protected $model = \App\Models\Resource::class;

    public function definition()
    {
        return [
            'lesson_id' => Lesson::inRandomOrder()->first()->id,
            'name' => $this->faker->sentence(2),
            'url' => $this->faker->url(),
        ];
    }
}
