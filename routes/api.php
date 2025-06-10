<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CronjobController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\PasswordResetController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\UploadAvatarController;
use App\Http\Controllers\API\UserActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API is working']);
});

Route::get('/vapid-public-key', function () {
    return response()->json([
        'publicKey' => config('webpush.vapid.public_key')
    ]);
});


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::post('/is-token-valid', [PasswordResetController::class, 'isTokenValid']);


Route::get('/cron/update-expired-task', [CronjobController::class, 'updateExpiredTask']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::delete('/user/delete', [AuthController::class, 'deleteAccount']);

    Route::get('/user/activity', [UserActivityController::class, 'getUserActivity']);

    Route::post('/user/upload-avatar', [UploadAvatarController::class, 'uploadAvatar']);

    Route::patch('/user/change-password', [PasswordController::class, 'changePassword']);

    Route::patch('/user/update-profile', [AuthController::class, 'patchProfile']);

    Route::get('/task/categories', [TaskController::class, 'getCategoryUser']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks', [TaskController::class, 'get']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // notification mabar
    Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
    Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
});
