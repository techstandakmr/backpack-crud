<?php

use App\Http\Controllers\Admin\CourseCrudController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EnrollmentCrudController;
use App\Http\Controllers\Admin\LessonCrudController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ResourceCrudController;
use App\Http\Controllers\Admin\UserCrudController;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('course', 'CourseCrudController');
    Route::crud('lesson', 'LessonCrudController');
    Route::crud('resource', 'ResourceCrudController');
    Route::crud('enrollment', 'EnrollmentCrudController');
    Route::crud('user', 'UserCrudController');
    // custom routes for courses
    Route::get('course/custom-view', [CourseCrudController::class, 'customView'])
        ->name('admin.course.custom');
    // custom routes for enrollments listing
    Route::get('enrollment/custom-view', [EnrollmentCrudController::class, 'customView'])
        ->name('admin.enrollment.custom');
    // custom routes for lessons listing
    Route::get('lesson/custom-view', [LessonCrudController::class, 'customView'])
        ->name('admin.lesson.custom');
    // custom routes for resources listing
    Route::get('resource/custom-view', [ResourceCrudController::class, 'customView'])
        ->name('admin.resource.custom');
    // custom routes for users listing
    Route::get('user/custom-view', [UserCrudController::class, 'customView'])
        ->name('admin.user.custom');
    // custom routes for reports
    Route::get('report', [ReportController::class, 'index'])->name('report.index');
    // custom routes for report exports files
    Route::get('report/export', [ReportController::class, 'export'])->name('report.export');
    // Route::get('enrollment/{id}/export-pdf', [EnrollmentCrudController::class, 'exportPdf'])
    //     ->name('enrollment.export.pdf');
    // custom routes for course & enrollment details of user
    Route::get('user/{id}/export-pdf', [UserCrudController::class, 'exportPdf'])->name('user.export.pdf');
    Route::get('user/{id}/export-csv', [UserCrudController::class, 'exportCsv'])->name('user.export.csv');
    Route::get('user/{id}/export-excel', [UserCrudController::class, 'exportExcel'])->name('user.export.excel');
});
