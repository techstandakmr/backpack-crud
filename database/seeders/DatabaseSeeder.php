<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use App\Models\Enrollment;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create some users
        $admins = User::factory()->count(2)->create(['role' => 'admin']);
        $teachers = User::factory()->count(5)->create(['role' => 'teacher']);
        $students = User::factory()->count(15)->create(['role' => 'student']);

        // Create courses for teachers
        $courses = Course::factory()->count(10)->create();

        // Create lessons and resources
        $courses->each(function ($course) {
            $lessons = \App\Models\Lesson::factory()->count(3)->create(['course_id' => $course->id]);

            $lessons->each(function ($lesson) {
                \App\Models\Resource::factory()->count(2)->create(['lesson_id' => $lesson->id]);
            });
        });

        // Enroll random students into random courses
        foreach (range(1, 20) as $i) {
            Enrollment::factory()->create();
        }
    }
}
