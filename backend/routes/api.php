<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\MedicationController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('user', [AuthController::class, 'user'])->name('auth.user');

    Route::get('medications/search', [MedicationController::class, 'search'])
        ->name('medications.search');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('export', [OrderController::class, 'export'])->name('export');
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('{id}', [OrderController::class, 'show'])->name('show');
    });

    Route::get('customers/{id}', [CustomerController::class, 'show'])
        ->name('customers.show');

    Route::prefix('alerts')->name('alerts.')->group(function () {
        Route::get('/', [AlertController::class, 'index'])->name('index');
        Route::post('send', [AlertController::class, 'send'])->name('send');
        Route::post('send-bulk', [AlertController::class, 'sendBulk'])->name('send-bulk');
    });

    Route::middleware('role:admin')->prefix('audit-logs')->name('audit-logs.')->group(function () {
        Route::get('/', [AuditLogController::class, 'index'])->name('index');
    });
});
