<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use CrudTrait;
    use HasFactory;
    protected $fillable = [
        'course_id',
        'student_name',
        'student_email',
        'phone',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
