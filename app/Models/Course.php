<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $fillable = ['title', 'description', 'author_id'];

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
    // Relationship: A course belongs to one author (User)
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
    // Automatically delete related lessons/enrollments
    protected static function booted()
    {
        static::deleting(function ($course) {
            $course->lessons()->delete();
            $course->enrollments()->delete();
        });
    }
}
