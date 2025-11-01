<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    use CrudTrait;
     use HasFactory;
    protected $fillable = ['name', 'lesson_id', 'url'];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

}
