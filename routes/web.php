<?php

use App\Http\Controllers\CarImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarInspectionController;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/upload-car', function () {
    return view('upload'); // the Blade file name
});
Route::post('/upload-car', [CarInspectionController::class, 'upload'])->name('car.upload');



Route::get('/upload', [CarImageController::class, 'index']);
Route::post('/upload', [CarImageController::class, 'analyze'])->name('upload.analyze');
