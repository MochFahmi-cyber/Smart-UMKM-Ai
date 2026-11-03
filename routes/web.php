<?php

use App\Http\Controllers\AIAssistantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerCashierController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::middleware(['auth'])->group(function () {
    Route::post('/premium/confirm-payment', [PremiumController::class, 'confirmPayment'])->name('premium.confirm-payment');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/admin/ai-assistant', [AIAssistantController::class, 'index'])->name('ai-assistant.index');
    Route::post('/admin/ai-assistant/send', [AIAssistantController::class, 'send'])->name('ai-assistant.send');
    Route::delete('/admin/ai-assistant/messages', [AIAssistantController::class, 'clearChat'])->name('ai-assistant.clear');

    Route::middleware(['kasir'])->group(function () {
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);
        Route::resource('customers', CustomerController::class);

        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos/cart', [PosController::class, 'addToCart'])->name('pos.cart.add');
        Route::patch('pos/cart/{product}', [PosController::class, 'updateCart'])->name('pos.cart.update');
        Route::delete('pos/cart/{product}', [PosController::class, 'removeCart'])->name('pos.cart.remove');
        Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
        Route::get('pos/receipt/{transaction}', [PosController::class, 'receipt'])->name('pos.receipt');

        Route::get('transactions/export/excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');
        Route::get('transactions/export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
        Route::get('transactions/{transaction}/print', [TransactionController::class, 'print'])->name('transactions.print');
        Route::resource('transactions', TransactionController::class)->only(['index', 'show', 'destroy']);
        Route::patch('transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])->name('transactions.update-status');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('reports/cash-flow', [ReportController::class, 'storeCashFlow'])->name('reports.cash-flow.store');
        Route::post('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::post('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');

        // Admin QR Kasir
        Route::get('qr-kasir', [\App\Http\Controllers\QrKasirController::class, 'index'])->name('qr-kasir.index');
        Route::post('qr-kasir/generate', [\App\Http\Controllers\QrKasirController::class, 'generate'])->name('qr-kasir.generate');
        Route::patch('qr-kasir/{token}/toggle', [\App\Http\Controllers\QrKasirController::class, 'toggleActive'])->name('qr-kasir.toggle');
    });

    Route::middleware(['admin'])->group(function () {
        Route::resource('users', UserController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/payment', [ProfileController::class, 'updatePaymentSettings'])->name('profile.update-payment');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Customer Kasir (No Auth Required)
Route::get('/customer/kasir/{token}', [CustomerCashierController::class, 'index'])->name('customer-kasir.index');
Route::post('/customer/kasir/{token}/cart', [CustomerCashierController::class, 'addToCart'])->name('customer-kasir.cart.add');
Route::patch('/customer/kasir/{token}/cart/{product_id}', [CustomerCashierController::class, 'updateCart'])->name('customer-kasir.cart.update');
Route::delete('/customer/kasir/{token}/cart/{product_id}', [CustomerCashierController::class, 'removeCart'])->name('customer-kasir.cart.remove');
Route::post('/customer/kasir/{token}/checkout', [CustomerCashierController::class, 'checkout'])->name('customer-kasir.checkout');
Route::get('/customer/kasir/{token}/order/{invoice}', [CustomerCashierController::class, 'success'])->name('customer-kasir.success');
Route::post('/customer/kasir/{token}/success/{invoice}/confirm', [CustomerCashierController::class, 'confirmPayment'])->name('customer-kasir.confirm-payment');

require __DIR__.'/auth.php';
