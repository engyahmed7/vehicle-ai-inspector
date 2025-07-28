<?php

use App\Http\Controllers\CarImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarInspectionController;

Route::get('/', function () {
    return redirect()->route('upload.index');
});



Route::get('/upload-car', function () {
    return view('upload');
});
// Route::post('/upload-car', [CarInspectionController::class, 'upload'])->name('car.upload');



Route::get('/upload', [CarImageController::class, 'index'])->name('upload.index');
Route::post('/upload', [CarImageController::class, 'analyze'])->name('upload.analyze');
