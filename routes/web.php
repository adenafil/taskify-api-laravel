<?php

use App\Http\Controllers\API\AuthSocialiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return "API is active on /api endpoint";
});

Route::get('/login/{service}', [AuthSocialiteController::class, 'redirect']);
Route::post('/login/{service}/callback', [AuthSocialiteController::class, 'callback']);


Route::get('/callback/{service}', [AuthSocialiteController::class, 'callback']);



