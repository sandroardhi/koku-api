<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\RoleController;

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
    Route::group(['middleware' => ['role:admin']], function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
            Route::put('/user/{id}/update-role', [AdminController::class, 'update_role'])->name('admin.update-role');
        });
        Route::prefix('kategori')->group(function () {
            Route::post('/store-kategori', [KategoriController::class, 'store'])->name('kategori.store');
            Route::put('/update-kategori/{id}', [KategoriController::class, 'update'])->name('kategori.update');
            Route::delete('/delete-kategori/{id}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
        });
        Route::apiResource('roles', RoleController::class);
        Route::get('/roles/fetch/permission', [RoleController::class, 'fetch_permission'])->name('roles.fetch_permission');
        Route::get('/roles/fetch-edit-data/{id}', [RoleController::class, 'fetch_role_edit_data'])->name('roles.fetch_role_edit_data');
    });
    Route::get('kategori/fetch-kategori', [KategoriController::class, 'index'])->name('kategori.index');
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthenticationController::class, 'profile'])->name('auth.profile');
        Route::get('/logout', [AuthenticationController::class, 'logout'])->name('auth.logout');
    });
    Route::group(['middleware' => ['role:penjual|admin']], function () {
        Route::prefix('produk')->group(function () {
            Route::get('/{id}', [ProdukController::class, 'show_produk'])->name('produk.show_produk');
            Route::post('/{id}', [ProdukController::class, 'store'])->name('produk.store');
            Route::put('/{id}', [ProdukController::class, 'update'])->name('produk.update');
            Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        });
    });
    Route::apiResource('kantin', KantinController::class)->except(['index']);
    Route::get('/kantin/profile/{id}', [KantinController::class, 'show_profile_kantin'])->name('profile.kantin');
});
