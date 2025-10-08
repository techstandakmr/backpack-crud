<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// Your custom route first
// Route::get('course/custom-view', [CourseCrudController::class, 'customView'])
//     ->name('admin.course.custom');