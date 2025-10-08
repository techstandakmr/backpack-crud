<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Resource;
use App\Models\Enrollment;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 6 authors
        User::factory(6)->create();

        // 6 courses
        Course::factory(6)->create();

        // 6 lessons per course
        foreach(Course::all() as $course){
            Lesson::factory(6)->create(['course_id' => $course->id]);
        }

        // 6 resources per lesson
        foreach(Lesson::all() as $lesson){
            Resource::factory(6)->create(['lesson_id' => $lesson->id]);
        }

        // 6 enrollments per course
        foreach(Course::all() as $course){
            Enrollment::factory(6)->create(['course_id' => $course->id]);
        }
    }
}
