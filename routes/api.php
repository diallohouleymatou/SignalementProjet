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

    // ========================================
    // CATEGORY ROUTES
    // ========================================
    Route::prefix('categories')->group(function () {
        Route::get('/', [\App\Modules\Category\Controllers\CategoryController::class, 'index']); // Get all categories
        Route::get('tree', [\App\Modules\Category\Controllers\CategoryController::class, 'tree']); // Get categories tree
        Route::get('popular', [\App\Modules\Category\Controllers\CategoryController::class, 'popular']); // Get popular categories
        Route::get('{id}', [\App\Modules\Category\Controllers\CategoryController::class, 'show']); // Get specific category
        Route::post('/', [\App\Modules\Category\Controllers\CategoryController::class, 'store']); // Create category (Admin)
        Route::put('{id}', [\App\Modules\Category\Controllers\CategoryController::class, 'update']); // Update category (Admin)
        Route::delete('{id}', [\App\Modules\Category\Controllers\CategoryController::class, 'destroy']); // Delete category (Admin)
    });

    // ========================================
    // MEDIA ROUTES
    // ========================================
    Route::prefix('media')->group(function () {
        Route::get('/', [\App\Modules\Media\Controllers\MediaController::class, 'index']); // Get media
        Route::get('{id}', [\App\Modules\Media\Controllers\MediaController::class, 'show']); // Get specific media
        Route::post('signalement/{signalementId}', [\App\Modules\Media\Controllers\MediaController::class, 'uploadForSignalement']); // Upload for signalement
        Route::post('profile-photo', [\App\Modules\Media\Controllers\MediaController::class, 'uploadProfilePhoto']); // Upload profile photo
        Route::post('reorder', [\App\Modules\Media\Controllers\MediaController::class, 'reorder']); // Reorder media
        Route::post('{id}/optimize', [\App\Modules\Media\Controllers\MediaController::class, 'optimize']); // Optimize image
        Route::delete('{id}', [\App\Modules\Media\Controllers\MediaController::class, 'destroy']); // Delete media
    });

    // ========================================
    // NOTIFICATION ROUTES
    // ========================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Modules\Notification\Controllers\NotificationController::class, 'index']); // Get all notifications
        Route::get('unread-count', [\App\Modules\Notification\Controllers\NotificationController::class, 'unreadCount']); // Get unread count
        Route::get('{id}', [\App\Modules\Notification\Controllers\NotificationController::class, 'show']); // Get specific notification
        Route::put('{id}/read', [\App\Modules\Notification\Controllers\NotificationController::class, 'markAsRead']); // Mark as read
        Route::put('{id}/unread', [\App\Modules\Notification\Controllers\NotificationController::class, 'markAsUnread']); // Mark as unread
        Route::post('mark-all-read', [\App\Modules\Notification\Controllers\NotificationController::class, 'markAllAsRead']); // Mark all as read
        Route::delete('{id}', [\App\Modules\Notification\Controllers\NotificationController::class, 'destroy']); // Delete notification
        Route::delete('read/all', [\App\Modules\Notification\Controllers\NotificationController::class, 'deleteAllRead']); // Delete all read
        Route::delete('all/clear', [\App\Modules\Notification\Controllers\NotificationController::class, 'deleteAll']); // Delete all
    });

    // ========================================
    // COMMENT ROUTES
    // ========================================
    Route::prefix('signalements/{signalementId}/comments')->group(function () {
        Route::get('/', [\App\Modules\Comment\Controllers\CommentController::class, 'index']); // Get all comments
        Route::post('/', [\App\Modules\Comment\Controllers\CommentController::class, 'store']); // Create comment
    });

    Route::prefix('comments')->group(function () {
        Route::put('{id}', [\App\Modules\Comment\Controllers\CommentController::class, 'update']); // Update comment
        Route::delete('{id}', [\App\Modules\Comment\Controllers\CommentController::class, 'destroy']); // Delete comment
        Route::post('{id}/like', [\App\Modules\Comment\Controllers\CommentController::class, 'like']); // Like comment
        Route::get('{id}/replies', [\App\Modules\Comment\Controllers\CommentController::class, 'replies']); // Get replies
        Route::put('{id}/approve', [\App\Modules\Comment\Controllers\CommentController::class, 'approve']); // Approve (Moderator)
        Route::put('{id}/reject', [\App\Modules\Comment\Controllers\CommentController::class, 'reject']); // Reject (Moderator)
    });

    // ========================================
    // REPORT ROUTES
    // ========================================
    Route::prefix('reports')->group(function () {
        Route::get('/', [\App\Modules\Report\Controllers\ReportController::class, 'index']); // Get all reports (Moderator)
        Route::get('pending-count', [\App\Modules\Report\Controllers\ReportController::class, 'pendingCount']); // Get pending count (Moderator)
        Route::get('{id}', [\App\Modules\Report\Controllers\ReportController::class, 'show']); // Get specific report (Moderator)
        Route::post('/', [\App\Modules\Report\Controllers\ReportController::class, 'store']); // Create report
        Route::put('{id}/review', [\App\Modules\Report\Controllers\ReportController::class, 'markAsReviewed']); // Mark as reviewed (Moderator)
        Route::put('{id}/resolve', [\App\Modules\Report\Controllers\ReportController::class, 'markAsResolved']); // Mark as resolved (Moderator)
        Route::put('{id}/reject', [\App\Modules\Report\Controllers\ReportController::class, 'markAsRejected']); // Mark as rejected (Moderator)
        Route::delete('{id}', [\App\Modules\Report\Controllers\ReportController::class, 'destroy']); // Delete report (Moderator)
    });

    // ========================================
    // FAVORITE ROUTES
    // ========================================
    Route::prefix('favorites')->group(function () {
        Route::get('/', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'index']); // Get all favorites
        Route::get('count', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'count']); // Get favorites count
        Route::post('{signalementId}', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'store']); // Add to favorites
        Route::delete('{signalementId}', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'destroy']); // Remove from favorites
        Route::post('{signalementId}/toggle', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'toggle']); // Toggle favorite
        Route::get('{signalementId}/check', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'check']); // Check if favorited
        Route::delete('all/clear', [\App\Modules\Favorite\Controllers\FavoriteController::class, 'clear']); // Clear all favorites
    });

    // ========================================
    // ADMIN ROUTES
    // ========================================
    Route::prefix('admin')->group(function () {
        // Dashboard
        Route::get('dashboard/statistics', [\App\Modules\Admin\Controllers\DashboardController::class, 'statistics']);
        Route::get('dashboard/activity', [\App\Modules\Admin\Controllers\DashboardController::class, 'recentActivity']);
        Route::get('dashboard/signalements-growth', [\App\Modules\Admin\Controllers\DashboardController::class, 'signalementsGrowth']);
        Route::get('dashboard/users-growth', [\App\Modules\Admin\Controllers\DashboardController::class, 'usersGrowth']);
        Route::get('dashboard/top-users', [\App\Modules\Admin\Controllers\DashboardController::class, 'topUsers']);
        Route::get('dashboard/popular-signalements', [\App\Modules\Admin\Controllers\DashboardController::class, 'popularSignalements']);
        Route::get('dashboard/system-health', [\App\Modules\Admin\Controllers\DashboardController::class, 'systemHealth']);

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [\App\Modules\Admin\Controllers\UserManagementController::class, 'index']);
            Route::get('{id}', [\App\Modules\Admin\Controllers\UserManagementController::class, 'show']);
            Route::put('{id}', [\App\Modules\Admin\Controllers\UserManagementController::class, 'update']);
            Route::post('{id}/ban', [\App\Modules\Admin\Controllers\UserManagementController::class, 'ban']);
            Route::post('{id}/unban', [\App\Modules\Admin\Controllers\UserManagementController::class, 'unban']);
            Route::post('{id}/verify', [\App\Modules\Admin\Controllers\UserManagementController::class, 'verify']);
            Route::put('{id}/role', [\App\Modules\Admin\Controllers\UserManagementController::class, 'changeRole']);
            Route::delete('{id}', [\App\Modules\Admin\Controllers\UserManagementController::class, 'destroy']);
            Route::delete('{id}/force', [\App\Modules\Admin\Controllers\UserManagementController::class, 'forceDelete']);
            Route::post('{id}/restore', [\App\Modules\Admin\Controllers\UserManagementController::class, 'restore']);
        });
    });
});
