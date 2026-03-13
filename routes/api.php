<?php

use App\Http\Controllers\Api\AllergenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ScanLogController;
use Illuminate\Support\Facades\Route;

// Auth
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

// Authenticated routes
Route::middleware('auth:api')->group(function () {

    // Allergens — full list + user's own allergen profile
    Route::get('allergens', [AllergenController::class, 'index']);
    Route::get('allergens/me', [AllergenController::class, 'myAllergens']);
    Route::put('allergens/me', [AllergenController::class, 'updateMyAllergens']);

    // Profiles — people the user shops for
    Route::get('profiles', [ProfileController::class, 'index']);
    Route::post('profiles', [ProfileController::class, 'store']);
    Route::get('profiles/{profile}', [ProfileController::class, 'show']);
    Route::put('profiles/{profile}', [ProfileController::class, 'update']);
    Route::delete('profiles/{profile}', [ProfileController::class, 'destroy']);
    Route::put('profiles/{profile}/allergens', [ProfileController::class, 'updateAllergens']);

    // Scan
    Route::post('scan', [ProductController::class, 'scan']);

    // Scan history
    Route::get('scan-logs', [ScanLogController::class, 'index']);
    Route::get('scan-logs/{scanLog}', [ScanLogController::class, 'show']);
});