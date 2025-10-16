<?php

use App\Http\Controllers\FileExtractController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/file-uploader', [FileExtractController::class, 'index']);
Route::post('/extract-file-text', [FileExtractController::class, 'extract'])->name('file.text.extract');
