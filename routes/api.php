<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('guest')->group(function () {
    Route::post('/auth/login', [AuthenticationController::class, 'login'])->name('auth.login');
    Route::post('/auth/register', [AuthenticationController::class, 'register'])->name('auth.register');
    Route::apiResource('kantin', KantinController::class)->only(['index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('kantin', KantinController::class)->except(['index']);
    Route::get('/kantin/profile/{id}', [KantinController::class, 'show_profile_kantin'])->name('profile.kantin');
    Route::get('/auth/profile', [AuthenticationController::class, 'profile'])->name('auth.profile');
    Route::get('/auth/logout', [AuthenticationController::class, 'logout'])->name('auth.logout');
    Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
});
