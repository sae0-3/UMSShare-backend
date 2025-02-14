<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('api.auth')->group(function () {
  Route::get('/user', [UserController::class, 'getProfile']);
  Route::post('/logout', [AuthController::class, 'logout']);
});
