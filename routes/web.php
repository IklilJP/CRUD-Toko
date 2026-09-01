<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KeranjangController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('products/arsip', [ProductController::class, 'arsip'])
    ->name('products.arsip');

Route::resource('products', ProductController::class)->except(['update', 'destroy']);

Route::post('products/{product}/update', [ProductController::class, 'update'])
    ->name('products.update');

Route::post('products/{product}/delete', [ProductController::class, 'destroy'])
    ->name('products.destroy');

Route::post('products/{product}/nonaktifkan', [ProductController::class, 'nonaktifkan'])
    ->name('products.nonaktifkan');

Route::post('products/{product}/aktifkan', [ProductController::class, 'aktifkan'])
    ->name('products.aktifkan');

Route::resource('categories', CategoryController::class)->except(['show', 'update', 'destroy']);

Route::post('categories/{category}/update', [CategoryController::class, 'update'])
    ->name('categories.update');

Route::post('categories/{category}/delete', [CategoryController::class, 'destroy'])
    ->name('categories.destroy');

Route::post('products/{product}/images/{image}/delete', [ProductImageController::class, 'destroy'])
    ->name('products.images.destroy');

Route::post('products/{product}/images/{image}/primary', [ProductImageController::class, 'setPrimary'])
    ->name('products.images.primary');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    // ini masih tes //
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('daftar');

    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:5,1');
    //xxxxxx//
});

           // route percobaan //
Route::get('keranjang', [KeranjangController::class, 'index'])
    ->name('keranjang.index');

Route::post('keranjang/kosongkan', [KeranjangController::class, 'kosongkan'])
    ->name('keranjang.kosongkan');

Route::post('keranjang/tambah/{product}', [KeranjangController::class, 'tambah'])
    ->name('keranjang.tambah');

Route::post('keranjang/{keranjang}/ubah', [KeranjangController::class, 'ubah'])
    ->name('keranjang.ubah');

Route::post('keranjang/{keranjang}/hapus', [KeranjangController::class, 'hapus'])
    ->name('keranjang.hapus');

Route::get('transaksi', [TransaksiController::class, 'index'])
    ->name('transaksi.index');

Route::get('checkout', [TransaksiController::class, 'checkout'])
    ->name('transaksi.checkout');

Route::post('checkout', [TransaksiController::class, 'store'])
    ->name('transaksi.store');

Route::get('transaksi/{transaksi}', [TransaksiController::class, 'show'])
    ->name('transaksi.show');

Route::post('transaksi/{transaksi}/status', [TransaksiController::class, 'ubahStatus'])
    ->name('transaksi.status');

Route::post('transaksi/{transaksi}/batal', [TransaksiController::class, 'batal'])
    ->name('transaksi.batal');
//        ///

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


Route::fallback(function () {
    return redirect('/');
});
