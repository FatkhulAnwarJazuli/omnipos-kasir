<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DiscountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;

// Route Aplikasi (Bebas Testing tanpa wajib Login)
Route::resource('stores', StoreController::class);
Route::resource('products', ProductController::class);
Route::post('/products/{product}/add-batch', [ProductController::class, 'addBatch'])->name('products.add-batch');
Route::post('/products/{product}/update-templates', [ProductController::class, 'updateTemplates'])->name('products.update-templates');
Route::post('/products/{product}/stock-opname', [ProductController::class, 'stockOpname'])->name('products.stock-opname');

// POS & Void Transaksi
Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
Route::post('/transactions/{transaction}/void', [PosController::class, 'voidTransaction'])->name('transactions.void');

Route::resource('categories', CategoryController::class);
Route::resource('expenses', ExpenseController::class)->except(['create', 'edit', 'show']);
Route::resource('suppliers', SupplierController::class)->except(['create', 'edit', 'show']);
Route::resource('discounts', DiscountController::class)->except(['create', 'edit', 'show']);
Route::post('/discounts/{discount}/toggle', [DiscountController::class, 'toggle'])->name('discounts.toggle');
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('/reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
Route::get('/reports/print', [ReportController::class, 'printPdf'])->name('reports.print');
// Dashboard sebagai halaman utama
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index']);

require __DIR__.'/auth.php';