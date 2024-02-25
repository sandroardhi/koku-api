<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\KantinController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PengantarController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\RoleController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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

Route::post('/forgotpassword', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::post('/resetpassword', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? response()->json(['message' => 'Password Berhasil Direset', 200])
        : response()->json(['message' => 'Password Berhasil Direset', 400]);
})->middleware('guest')->name('password.update');

Route::middleware('guest')->group(function () {
    Route::post('/auth/login', [AuthenticationController::class, 'login'])->name('auth.login');
    Route::post('/auth/register', [AuthenticationController::class, 'register'])->name('auth.register');
    Route::post('/auth/register-penjual', [AuthenticationController::class, 'registerPenjual'])->name('auth.registerPenjual');
    Route::apiResource('kantin', KantinController::class)->only(['index', 'show']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::group(['middleware' => ['role:admin']], function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
            Route::put('/user/{id}/update-role', [AdminController::class, 'update_role'])->name('admin.update-role');
            Route::get('/uang-masuk', [DashboardController::class, 'fetchUangBelumDibayar'])->name('order.fetchUangBelumDibayar');
            Route::get('/uang-selesai', [DashboardController::class, 'fetchUangSelesai'])->name('order.fetchUangSelesai');
            Route::get('/uang-penjual', [DashboardController::class, 'fetchUangPenjual'])->name('order.fetchUangPenjual');
            Route::get('/uang-pengantar', [DashboardController::class, 'fetchUangPengantar'])->name('order.fetchUangPengantar');
            Route::get('/uang-refund', [DashboardController::class, 'fetchUangRefund'])->name('order.fetchUangRefund');
            Route::put('/bayar-penjual', [DashboardController::class, 'bayarPenjual'])->name('order.bayarPenjual');
            Route::put('/bayar-pengantar', [DashboardController::class, 'bayarPengantar'])->name('order.bayarPengantar');
            Route::put('/bayar-refund', [DashboardController::class, 'bayarRefund'])->name('order.bayarRefund');
            Route::get('/dashboard', [DashboardController::class, 'dashboardAdmin'])->name('order.dashboardAdmin');
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
        Route::put('/update-rekening', [AuthenticationController::class, 'update_rekening'])->name('auth.update_rekening');
        Route::get('/logout', [AuthenticationController::class, 'logout'])->name('auth.logout');
    });
    Route::group(['middleware' => ['role:penjual']], function () {
        Route::prefix('produk')->group(function () {
            Route::get('/{id}', [ProdukController::class, 'show_produk'])->name('produk.show_produk');
            Route::post('/{id}', [ProdukController::class, 'store'])->name('produk.store');
            Route::put('/{id}', [ProdukController::class, 'update'])->name('produk.update');
            Route::delete('/{id}', [ProdukController::class, 'destroy'])->name('produk.destroy');
        });
    });
    Route::group(['middleware' => ['role:user']], function () {
        Route::put('/auth/register-pengantar', [AuthenticationController::class, 'registerPengantar'])->name('auth.registerPengantar');
        Route::prefix('order')->group(function () {
            Route::post('/pay-and-create', [OrderController::class, 'payAndCreateOrder'])->name('order.payAndCreateOrder');
            Route::get('/order-pending', [OrderController::class, 'OrderPending'])->name('order.OrderPending');
            Route::get('/order-proses', [OrderController::class, 'OrderProses'])->name('order.OrderProses');
            Route::get('/order-selesai', [OrderController::class, 'OrderSelesai'])->name('order.OrderSelesai');
            Route::post('/order-user-update-selesai', [OrderController::class, 'UserUpdateOrderSelesai'])->name('order.UserUpdateOrderSelesai');
            Route::post('/destroy', [OrderController::class, 'destroy'])->name('order.destroy');
            Route::post('/check-pengantar', [OrderController::class, 'checkPengantar'])->name('order.checkPengantar');
            Route::get('/uang-refund', [DashboardController::class, 'fetchUangRefundPembeli'])->name('order.fetchUangRefundPembeli');
        });
    });
    Route::group(['middleware' => ['role:penjual']], function () {
        Route::prefix('order-penjual')->group(function () {
            Route::get('/order-masuk', [OrderController::class, 'OrderPenjualMasuk'])->name('order.OrderPenjualMasuk');
            Route::get('/order-selesai', [OrderController::class, 'OrderPenjualSelesai'])->name('order.OrderPenjualSelesai');
            Route::get('/order-count', [OrderController::class, 'orderMasukCount'])->name('order.orderMasukCount');
            Route::get('/order-cancel', [OrderController::class, 'OrderPenjualCancel'])->name('order.OrderPenjualCancel');
            Route::post('/order-update-selesai', [OrderController::class, 'UpdateStatusOrderProdukSelesai'])->name('order.UpdateStatusOrderProdukSelesai');
            Route::post('/order-update-dibuat', [OrderController::class, 'UpdateStatusOrderProdukDibuat'])->name('order.UpdateStatusOrderProdukDibuat');
            Route::get('/uang-masuk', [DashboardController::class, 'fetchUangMasukPenjual'])->name('order.fetchUangMasukPenjual');
            Route::get('/dashboard', [DashboardController::class, 'dashboardPenjual'])->name('order.dashboardPenjual');
        });
    });
    Route::group(['middleware' => ['role:pengantar']], function () {
        Route::prefix('order-pengantar')->group(function () {
            Route::get('/order-masuk', [PengantarController::class, 'OrderPengantarMasuk'])->name('order.OrderPengantarMasuk');
            Route::get('/order-selesai', [PengantarController::class, 'OrderPengantarSelesai'])->name('order.OrderPengantarSelesai');
            Route::get('/order-cancel', [PengantarController::class, 'OrderPengantarCancel'])->name('order.OrderPengantarCancel');
            Route::post('/order-update-selesai', [PengantarController::class, 'UserUpdateOrderSelesai'])->name('order.UserUpdateOrderSelesai');
            Route::get('/order-count', [OrderController::class, 'orderPengantarCount'])->name('order.orderPengantarCount');
            Route::post('/toggle-active', [PengantarController::class, 'togglePengantarActive'])->name('order.togglePengantarActive');
            Route::post('/toggle-nonactive', [PengantarController::class, 'togglePengantarNonactive'])->name('order.togglePengantarNonactive');
            Route::get('/uang-masuk', [DashboardController::class, 'fetchUangMasukPengantar'])->name('order.fetchUangMasukPengantar');
            Route::get('/dashboard', [DashboardController::class, 'dashboardPengantar'])->name('order.dashboardPengantar');
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
