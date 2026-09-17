<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;

// Halaman Utama langsung mengarah ke Manajemen Toko (TailAdmin)
Route::get('/', function () {
    return redirect()->route('stores.index');
});

// Route Aplikasi (Bebas Testing tanpa wajib Login)
Route::resource('stores', StoreController::class);
Route::resource('products', ProductController::class);
Route::get('/products/{product}/add-batch', [ProductController::class, 'addBatch'])->name('products.add-batch');
Route::post('/products/{product}/store-batch', [ProductController::class, 'storeBatch'])->name('products.store-batch');
Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
Route::get('/dashboard', function () {
    return redirect()->route('stores.index');
})->name('dashboard');
Route::resource('categories', CategoryController::class);

require __DIR__.'/auth.php';

