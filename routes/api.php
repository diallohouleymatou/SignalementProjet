<?php

use App\Modules\User\Controllers\AuthController;
use App\Modules\User\Controllers\UserController;
use App\Modules\Signalement\Controllers\SignalementController;
use App\Modules\Message\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ========================================
// PUBLIC ROUTES (No Authentication)
// ========================================

// Authentication routes
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// ========================================
// PROTECTED ROUTES (Require Authentication)
// ========================================

Route::middleware(['auth:api'])->group(function () {

    // ========================================
    // AUTH ROUTES
    // ========================================
    Route::get('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);

    // ========================================
    // USER ROUTES
    // ========================================
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']); // Get all users
        Route::get('profile', [UserController::class, 'profile']); // Get own profile
        Route::post('profile', [UserController::class, 'updateProfile']); // Update own profile
        Route::get('statistics', [UserController::class, 'statistics']); // Get user statistics
        Route::get('{id}', [UserController::class, 'show']); // Get specific user
    });

    // ========================================
    // SIGNALEMENT ROUTES
    // ========================================
    Route::prefix('signalements')->group(function () {
        Route::get('/', [SignalementController::class, 'index']); // Get all signalements
        Route::get('my', [SignalementController::class, 'mySignalements']); // Get my signalements
        Route::get('search', [SignalementController::class, 'search']); // Search signalements
        Route::get('status/{status}', [SignalementController::class, 'byStatus']); // Get by status
        Route::get('type/{type}', [SignalementController::class, 'byType']); // Get by type
        Route::get('{id}', [SignalementController::class, 'show']); // Get specific signalement
        Route::post('/', [SignalementController::class, 'store']); // Create signalement
        Route::put('{id}', [SignalementController::class, 'update']); // Update signalement
        Route::delete('{id}', [SignalementController::class, 'destroy']); // Delete signalement
    });

    // ========================================
    // MESSAGE ROUTES
    // ========================================
    Route::prefix('messages')->group(function () {
        Route::post('/', [MessageController::class, 'store']); // Send message
        Route::get('conversations', [MessageController::class, 'conversations']); // Get all conversations
        Route::get('unread-count', [MessageController::class, 'unreadCount']); // Get unread count
        Route::get('signalement/{signalement_id}', [MessageController::class, 'index']); // Get messages for signalement
        Route::get('conversation/{signalement_id}/{user_id}', [MessageController::class, 'conversationWith']); // Get conversation with user
        Route::put('{id}/read', [MessageController::class, 'markAsRead']); // Mark as read
        Route::put('signalement/{signalement_id}/read-all', [MessageController::class, 'markAllAsRead']); // Mark all as read
    });
});
