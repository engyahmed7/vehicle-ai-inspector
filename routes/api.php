<?php

use App\Http\Controllers\InsuranceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/webhooks/canopy', [InsuranceController::class, 'handle']);
