<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VisionController;
use App\Http\Controllers\Api\TraitController;
use App\Http\Controllers\Api\ActionController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\TimelineController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CommunityController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\LanguageController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Google OAuth routes (public)
Route::get('/auth/google/redirect', [AuthController::class, 'googleRedirect']);
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback']);
Route::post('/auth/google/token', [AuthController::class, 'googleToken']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::put('/auth/user', [AuthController::class, 'updateProfile']);

    // Language
    Route::get('/language', [LanguageController::class, 'index']);
    Route::get('/language/current', [LanguageController::class, 'current']);
    Route::post('/language/switch', [LanguageController::class, 'switch']);

    // Dashboard
    Route::get('/dashboard/today', [DashboardController::class, 'today']);

    // Vision (SEE)
    Route::get('/vision', [VisionController::class, 'show']);
    Route::post('/vision', [VisionController::class, 'store']);
    Route::get('/vision/history', [VisionController::class, 'history']);

    // Identity (BE) - Traits
    Route::get('/traits', [TraitController::class, 'library']);
    Route::get('/identity/traits', [TraitController::class, 'mine']); // Add identity endpoint
    Route::get('/identity/library', [TraitController::class, 'library']); // Add identity library endpoint
    Route::get('/traits/mine', [TraitController::class, 'mine']);
    Route::post('/traits/mine', [TraitController::class, 'addMine']);
    Route::put('/traits/mine/{id}', [TraitController::class, 'updateMine']);
    Route::delete('/traits/mine/{id}', [TraitController::class, 'removeMine']);
    Route::post('/traits/custom', [TraitController::class, 'createCustom']);

    // Actions (DO)
    Route::get('/actions', [ActionController::class, 'index']);
    Route::post('/actions', [ActionController::class, 'store']);
    Route::put('/actions/{id}', [ActionController::class, 'update']);
    Route::delete('/actions/{id}', [ActionController::class, 'destroy']);
    Route::post('/actions/{id}/check-in', [ActionController::class, 'checkIn']);
    Route::get('/actions/today', [ActionController::class, 'today']);
    Route::get('/actions/stats', [ActionController::class, 'stats']);

    // Journal - Fixed routes order
    Route::get('/journal/entries', [JournalController::class, 'index']); // Add specific entries endpoint
    Route::get('/journal', [JournalController::class, 'index']);
    Route::post('/journal', [JournalController::class, 'store']);
    Route::get('/journal/{id}', [JournalController::class, 'show']);
    Route::put('/journal/{id}', [JournalController::class, 'update']);
    Route::delete('/journal/{id}', [JournalController::class, 'destroy']);

    // Community/Feeds
    Route::get('/feeds', [CommunityController::class, 'feeds']);
    Route::get('/community/feeds', [CommunityController::class, 'feeds']); // Alternative endpoint
    Route::post('/community/posts', [CommunityController::class, 'createPost']);
    Route::post('/community/posts/{id}/like', [CommunityController::class, 'like']);
    Route::post('/community/posts/{id}/comment', [CommunityController::class, 'comment']);
    Route::get('/community/posts/{id}/comments', [CommunityController::class, 'comments']);

    // Calendar/Islamic Events
    Route::get('/calendar/events', [CalendarController::class, 'events']);
    Route::get('/islamic-events', [CalendarController::class, 'events']); // Alternative endpoint

    // Reviews
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/monthly', [ReviewController::class, 'monthly']);

    // Daily Check-ins (spiritual)
    Route::get('/check-ins', [CheckInController::class, 'index']);
    Route::post('/check-ins', [CheckInController::class, 'store']);
    Route::get('/check-ins/today', [CheckInController::class, 'today']);
    Route::get('/check-ins/{id}', [CheckInController::class, 'show']);
    Route::delete('/check-ins/{id}', [CheckInController::class, 'destroy']);

    // Timeline
    Route::get('/timeline', [TimelineController::class, 'index']);
});