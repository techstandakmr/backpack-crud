<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;

class EnrollmentFactory extends Factory
{
    protected $model = \App\Models\Enrollment::class;

    public function definition()
    {
        return [
            'course_id' => Course::inRandomOrder()->first()->id,
            'student_name' => $this->faker->name(),
            'student_email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
