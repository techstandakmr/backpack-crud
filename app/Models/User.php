<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use CrudTrait, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role', // Add this field so Backpack forms work with it
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Role helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    public function isStudent()
    {
        return $this->role === 'student';
    }

    // Relationships
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function coursesAuthored()
    {
        return $this->hasMany(Course::class, 'author_id');
    }
    // this shows the course's details in the user model
    public function coursesEnrolled()
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withTimestamps();
    }
}
