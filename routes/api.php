<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PengantarController;
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
    Route::apiResource('kantin', KantinController::class)->only(['index', 'show']);
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
    Route::prefix('auth')->group(function () {
        Route::get('/get-user', [AuthenticationController::class, 'getUser'])->name('auth.getUser');
        Route::get('/tujuan', [AuthenticationController::class, 'tujuan'])->name('auth.tujuan');
        Route::post('/create-tujuan', [AuthenticationController::class, 'create_tujuan'])->name('auth.create_tujuan');
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
    Route::group(['middleware' => ['role:user|admin']], function () {
        Route::prefix('order')->group(function () {
            Route::post('/pay-and-create', [OrderController::class, 'payAndCreateOrder'])->name('order.payAndCreateOrder');
            Route::get('/order-pending', [OrderController::class, 'OrderPending'])->name('order.OrderPending');
            Route::get('/order-proses', [OrderController::class, 'OrderProses'])->name('order.OrderProses');
            Route::get('/order-selesai', [OrderController::class, 'OrderSelesai'])->name('order.OrderSelesai');
            Route::post('/order-user-update-selesai', [OrderController::class, 'UserUpdateOrderSelesai'])->name('order.UserUpdateOrderSelesai');
            Route::post('/destroy', [OrderController::class, 'destroy'])->name('order.destroy');
            Route::post('/check-pengantar', [OrderController::class, 'checkPengantar'])->name('order.checkPengantar');
        });
    });
    Route::group(['middleware' => ['role:penjual|admin']], function () {
        Route::prefix('order-penjual')->group(function () {
            Route::get('/order-masuk', [OrderController::class, 'OrderPenjualMasuk'])->name('order.OrderPenjualMasuk');
            Route::get('/order-selesai', [OrderController::class, 'OrderPenjualSelesai'])->name('order.OrderPenjualSelesai');
            Route::get('/order-cancel', [OrderController::class, 'OrderPenjualCancel'])->name('order.OrderPenjualCancel');
            Route::post('/order-update-selesai', [OrderController::class, 'UpdateStatusOrderProdukSelesai'])->name('order.UpdateStatusOrderProdukSelesai');
            Route::post('/order-update-dibuat', [OrderController::class, 'UpdateStatusOrderProdukDibuat'])->name('order.UpdateStatusOrderProdukDibuat');
        });
    });
    Route::group(['middleware' => ['role:pengantar|admin']], function () {
        Route::prefix('order-pengantar')->group(function () {
            Route::get('/order-masuk', [PengantarController::class, 'OrderPengantarMasuk'])->name('order.OrderPengantarMasuk');
            Route::get('/order-selesai', [PengantarController::class, 'OrderPengantarSelesai'])->name('order.OrderPengantarSelesai');
            Route::get('/order-cancel', [PengantarController::class, 'OrderPengantarCancel'])->name('order.OrderPengantarCancel');
            Route::post('/order-update-selesai', [PengantarController::class, 'UpdateStatusOrderProdukSelesai'])->name('order.UpdateStatusOrderProdukSelesai');
            Route::post('/toggle-active', [PengantarController::class, 'togglePengantarActive'])->name('order.togglePengantarActive');
            Route::post('/toggle-nonactive', [PengantarController::class, 'togglePengantarNonactive'])->name('order.togglePengantarNonactive');
        });
    });
    Route::prefix('keranjang')->group(function () {
        Route::post('/add-to-cart', [KeranjangController::class, 'addToCart'])->name('keranjang.addToCart');
        Route::get('/get-cart-data', [KeranjangController::class, 'getCartData'])->name('keranjang.getCartData');
        Route::put('/update-kuantitas', [KeranjangController::class, 'updateKuantitas'])->name('keranjang.updateKuantitas');
        Route::delete('/delete', [KeranjangController::class, 'deleteCartProduct'])->name('keranjang.deleteCartProduct');
    });
    Route::apiResource('kantin', KantinController::class)->except(['index', 'show']);
    Route::get('/kantin/profile/{id}', [KantinController::class, 'show_profile_kantin'])->name('profile.kantin');
});
Route::get('/kantin/index/fetch-nama-kantin', [KantinController::class, 'fetch_kantin_name'])->name('kantin.fetch_kantin_name');
Route::get('kategori/fetch-kategori', [KategoriController::class, 'index'])->name('kategori.index');
Route::get('kategori/fetch-kategori-detail/{id}', [KategoriController::class, 'show'])->name('kategori.show');

Route::post('/order/callback', [OrderController::class, 'callback'])->name('order.callback');
