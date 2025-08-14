<?php

use App\Http\Controllers\CarImageController;
use App\Http\Controllers\CarListingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return redirect()->route('upload.index');
});

Route::get('/upload-car', function () {
    return view('upload');
});

Route::get('/upload', [CarImageController::class, 'index'])->name('upload.index');
Route::post('/upload', [CarImageController::class, 'analyze'])->name('upload.analyze');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/chat', function () {
    return view('chat');
})->middleware(['auth'])->name('chat');

Broadcast::routes(['middleware' => ['auth']]);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/cars', [CarListingController::class, 'index'])->name('cars.index');
    Route::get('/cars/{car}', [CarListingController::class, 'show'])->name('cars.show');

    Route::get('/api/conversations', [ChatController::class, 'getConversations']);
    Route::get('/api/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/api/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/api/conversations', [ChatController::class, 'startConversation']);
});

require __DIR__ . '/auth.php';
