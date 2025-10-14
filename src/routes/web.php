<?php

use Emizor\SDK\Http\Controllers\Admin\ConfigController;
use Emizor\SDK\Http\Controllers\Admin\InvoiceController;
use Illuminate\Support\Facades\Route;

// Admin routes for EMIZOR SDK
Route::prefix('emizor-admin')->middleware(['web'])->group(function () {
    // Accounts
    Route::get('accounts', [ConfigController::class, 'accounts'])->name('emizor.admin.accounts');
    Route::post('accounts/register', [ConfigController::class, 'register'])->name('emizor.admin.accounts.register');
    Route::post('accounts/{id}/set-defaults', [ConfigController::class, 'setDefaults'])->name('emizor.admin.accounts.set-defaults');

    // Invoices
    Route::get('invoices', [InvoiceController::class, 'index'])->name('emizor.admin.invoices.index');
    Route::get('invoices/{id}', [InvoiceController::class, 'show'])->name('emizor.admin.invoices.show');
    Route::post('invoices/{id}/fetch-pdf', [InvoiceController::class, 'fetchPdf'])->name('emizor.admin.invoices.fetch-pdf');
    Route::post('invoices/{id}/revocate', [InvoiceController::class, 'revocate'])->name('emizor.admin.invoices.revocate');

    // Configuration
    Route::get('config', [ConfigController::class, 'index'])->name('emizor.admin.config.index');
    Route::get('config/check', [ConfigController::class, 'check'])->name('emizor.admin.config.check');
    Route::post('config/sync/{type}', [ConfigController::class, 'sync'])->name('emizor.admin.config.sync');
});