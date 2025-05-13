<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AuthSocialiteController;
use App\Http\Controllers\API\PasswordController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\UploadAvatarController;
use App\Http\Controllers\API\UserActivityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'API is working']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');



// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/user/activity', [UserActivityController::class, 'getUserActivity']);

    Route::post('/user/upload-avatar', [UploadAvatarController::class, 'uploadAvatar']);

    Route::patch('/user/change-password', [PasswordController::class, 'changePassword']);

    Route::patch('/user/update-profile', [AuthController::class, 'patchProfile']);

    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/tasks', [TaskController::class, 'get']);
    Route::patch('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});
