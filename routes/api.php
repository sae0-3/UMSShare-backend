<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\UserController;

Route::get('auth/google', [GoogleController::class, 'getAuthUrl']);
Route::get('/auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::middleware('api.auth')->group(function () {
  Route::get('/user', [UserController::class, 'getProfile']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/upload-file', [GoogleDriveController::class, 'upload']);
  Route::post('/upload-files', [GoogleDriveController::class, 'uploadMultipleFiles']);
});
