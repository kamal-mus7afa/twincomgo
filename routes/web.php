<?php

use App\Http\Controllers\AccurateAccountController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AuthinticationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\ListController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ResellerController;
use App\Http\Controllers\SecondProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('items.index')
        : redirect()->route('auth.login');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthinticationController::class, 'index'])->name('auth.login');
    Route::post('/login', [AuthinticationController::class, 'login'])->name('auth.login.post');
});

Route::post('/logout', [AuthinticationController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| RESET PASSWORD (GABUNG & HILANGKAN DUPLIKAT)
|--------------------------------------------------------------------------
*/
Route::controller(ResetPasswordController::class)->group(function () {
    Route::get('/forgot-password', 'showForgotForm')->name('password.request');
    Route::post('/forgot-password', 'sendOtp')->name('password.email');

    Route::post('/resend-otp', 'resendOtp');
    Route::get('/verify-otp', 'showVerifyForm');
    Route::post('/verify-otp', 'verifyOtp');

    Route::get('/reset-password', 'showResetForm');
    Route::post('/reset-password', 'resetPassword')->name('password.update');
});

/*
|--------------------------------------------------------------------------
| AUTH REQUIRED (UMUM)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::get('/katalog', [CustomerController::class, 'index'])->name('katalog.items');

    // LIST
    Route::get('/list', [ListController::class, 'index']);
    Route::get('/list/search', [ListController::class, 'search']);
    Route::post('/list/add', [ListController::class, 'add']);
    Route::post('/list/clear', [ListController::class, 'clear']);
    Route::post('/list/remove', [ListController::class, 'remove']);
    Route::get('/list/pdf', [ListController::class, 'pdf']);

});

/*
|--------------------------------------------------------------------------
| KARYAWAN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'karyawan'])->group(function () {

    Route::get('/item', [ItemController::class, 'index'])->name('items.index');
    Route::get('/item/{encrypted}', [KaryawanController::class, 'show'])->name('karyawan.show');

    Route::get('/karyawan/{encrypted}/export-pdf', [KaryawanController::class, 'exportPdf'])->name('karyawan.exportPdf');
    Route::get('/karyawan/{id}/price', [KaryawanController::class, 'getPrice']);

    // AJAX
    Route::get('/ajax/item-image', [KaryawanController::class, 'getItemImage'])->name('ajax.item.image');
    Route::get('/ajax/price', [ItemController::class, 'ajaxPrice'])->name('ajax.price');

});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin-dashboard', [AdminController::class, 'index'])->name('admin.index');

    Route::get('/admin/item', [AdminController::class, 'indexItems'])->name('admin.items');
    Route::get('/admin/detail/{encrypted}', [AdminController::class, 'showItems'])->name('admin.detail');

    Route::get('/admin-user', [AdminController::class, 'viewUser'])->name('admin.user');
    Route::get('/admin-log', [AdminController::class, 'logActivity'])->name('admin.log');
    Route::get('/admin/log/user-search', [AdminController::class, 'searchUser'])->name('admin.log.user-search');

    Route::post('/auto-logout', [AdminController::class, 'autoLogout'])->name('auto.logout');

    // USER CRUD
    Route::get('/admin/users', [UserController::class, 'index'])->name('users2.index');
    Route::get('/admin/users/create', [UserController::class, 'create'])->name('users2.create');
    Route::post('/admin/users', [UserController::class, 'store'])->name('users2.store');
    Route::get('/admin/users/{id}/edit', [UserController::class, 'edit'])->name('users2.edit');
    Route::put('/admin/users/{id}', [UserController::class, 'update'])->name('users2.update');
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy'])->name('users2.destroy');

    // ACCURATE
    Route::get('/admin/accurate-accounts', [AccurateAccountController::class, 'index'])->name('aa.index');
    Route::get('/admin/accurate-accounts/create', [AccurateAccountController::class, 'create'])->name('aa.create');
    Route::post('/admin/accurate-accounts', [AccurateAccountController::class, 'store'])->name('aa.store');
    Route::get('/admin/accurate-accounts/{id}/edit', [AccurateAccountController::class, 'edit'])->name('aa.edit');
    Route::put('/admin/accurate-accounts/{id}', [AccurateAccountController::class, 'update'])->name('aa.update');
    Route::delete('/admin/accurate-accounts/{id}', [AccurateAccountController::class, 'destroy'])->name('aa.destroy');

    // EXTRA USERS
    Route::get('/users/create', [AdminController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminController::class, 'store'])->name('users.post');
    Route::get('/users/{id}', [AdminController::class, 'show'])->name('users.show');

    // GALERI & SIMULASI
    Route::get("/admin/simulasi/rakit-pc", [AdminController::class, 'indexRakitPc'])->name('admin.simulasi.rakitpc');
    Route::get("/admin/simulasi/rakit-cctv", [AdminController::class, 'indexRakitCctv'])->name('admin.simulasi.rakitcctv');

});

/*
|--------------------------------------------------------------------------
| RESELLER
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'reseller'])->group(function () {

    Route::get('/reseller', [ResellerController::class, 'index2'])->name('reseller.index');
    Route::get('/reseller/test', [ResellerController::class, 'index'])->name('reseller.test');
    Route::get('/reseller/{encrypted}', [ResellerController::class, 'show'])->name('reseller.detail');

    Route::get('/ajax/priceReseller', [ResellerController::class, 'ajaxPriceReseller'])->name('ajax.price.reseller');
});

/*
|--------------------------------------------------------------------------
| PUBLIC (TANPA AUTH - BIARKAN SESUAI ASLI)
|--------------------------------------------------------------------------
*/
Route::get('/ajax/warehouse-stock', [KaryawanController::class, 'getWarehouseStock'])->name('ajax.warehouse.stock');
Route::get('/proxy/image', [KaryawanController::class, 'proxyImage'])->name('proxy.image');
Route::get('/branches', [KaryawanController::class, 'getBranches']);
Route::get('/items/export-pdf', [ItemController::class, 'exportPdf1'])->name('items.exportPdf');
Route::get('/items/export-excel', [ItemController::class, 'exportExcel1'])->name('items.excel');


Route::get('/purchase-invoice', [SecondProductController::class, 'invoiceIndex'])->name('invoiceIndex');
Route::get('/purchase-invoice/detail', [SecondProductController::class, 'getDetailPurchaseInvoice'])->name('getInvoice');

Route::post('/second-products/store', [SecondProductController::class, 'store']);
Route::get('/branch', [SecondProductController::class, 'getBranch']);
Route::get('/warehouse', [SecondProductController::class, 'getWarehouse']);
Route::get('/customer', [SecondProductController::class, 'getCustomer']);

Route::get(
    '/second-products/{id}/edit',
    [SecondProductController::class, 'edit']
)->name('second.edit');

Route::get(
    '/second-products',
    [SecondProductController::class, 'index']
)->name('second.index');

Route::put(
    '/second-products/{id}',
    [SecondProductController::class, 'update']
)->name('second.update');

Route::delete('/second-products/{id}', [SecondProductController::class, 'destroy'])->name('second.destroy');
Route::get('/second-products/{id}/show', [SecondProductController::class, 'show'])->name('second.show');
Route::patch('/second-products/{id}/status', [SecondProductController::class, 'updateStatus'])->name('second.updateStatus');

Route::get('/daftar-product', [SecondProductController::class, 'daftarProduct'])->name('second.product');
Route::post('/second/{id}/keep', [SecondProductController::class, 'keep'])->name('second.keep');

Route::get('/cart', [OrderController::class, 'index'])->name('cart.index');
Route::get('/checkout', [OrderController::class, 'checkout'])
    ->name('checkout');