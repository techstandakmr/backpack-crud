<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Course;
use App\Models\User;

class EnrollmentFactory extends Factory
{
    protected $model = \App\Models\Enrollment::class;

    public function definition()
    {
        $student = User::where('role', 'student')->inRandomOrder()->first();
        $course = Course::inRandomOrder()->first();

        if (!$student) {
            $student = User::factory()->create(['role' => 'student']);
        }

        if (!$course) {
            $course = Course::factory()->create();
        }

        return [
            'course_id' => $course->id,
            'user_id' => $student->id,
        ];
    }
}
