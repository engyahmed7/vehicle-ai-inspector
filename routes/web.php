<?php

use App\Http\Controllers\CarImageController;
use App\Http\Controllers\CarListingController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MvrController;
use App\Http\Controllers\PersonaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/upload-car', function () {
    return view('upload');
});

Route::get('/upload', [CarImageController::class, 'index'])->name('upload.index');
Route::post('/upload', [CarImageController::class, 'analyze'])->name('upload.analyze');
Route::get('/results/{carId}', [CarImageController::class, 'showResults'])->middleware('auth')->name('results.show');
Route::get('/api/cars/{carId}/results', [CarImageController::class, 'getCarResults'])->name('cars.results');
Route::get('/api/user/cars', [CarImageController::class, 'getUserCars'])->middleware('auth')->name('user.cars');

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

    Route::get('/kyc', function () {
        return view('kyc');
    })->name('kyc.verify');

    Route::get('/api/conversations', [ChatController::class, 'getConversations']);
    Route::get('/api/conversations/{conversationId}/messages', [ChatController::class, 'getMessages']);
    Route::post('/api/conversations/{conversationId}/messages', [ChatController::class, 'sendMessage']);
    Route::post('/api/conversations', [ChatController::class, 'startConversation']);
});


Route::post('/persona/inquiry', [PersonaController::class, 'createInquiry'])->middleware('auth');
Route::get('/persona/inquiry/{id}', [PersonaController::class, 'checkInquiry'])->middleware('auth');
Route::get('/persona/status', [PersonaController::class, 'getCurrentStatus'])->middleware('auth');
Route::get('/persona/verification-url', [PersonaController::class, 'getVerificationUrl'])->middleware('auth');
Route::post('/persona/webhook', [PersonaController::class, 'webhook']);

Route::middleware('auth')->group(function () {
    Route::get('/mvr', [MvrController::class, 'index'])->name('mvr.index');
    Route::post('/mvr/check', [MvrController::class, 'createMvrCheck'])->name('mvr.check');
    Route::get('/mvr/results/{reportId}', [MvrController::class, 'getMvrResults'])->name('mvr.results');
    Route::get('/mvr/candidate/{candidateId}', [MvrController::class, 'getCandidate'])->name('mvr.candidate');
    Route::get('/mvr/candidate/{candidateId}/mvrs', [MvrController::class, 'listCandidateMvrs'])->name('mvr.candidate.mvrs');
    Route::post('/mvr/test', [MvrController::class, 'testMvr'])->name('mvr.test');
});

Route::post('/mvr/webhook', [MvrController::class, 'webhook'])->name('mvr.webhook');

require __DIR__ . '/auth.php';
