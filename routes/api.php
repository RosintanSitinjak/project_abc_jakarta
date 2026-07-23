<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORT CONTROLLER ---
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AttachmentController;

// 1. RUTE PUBLIK
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
    Route::post('visitor', [VisitorController::class, 'store']);
});

// 2. RUTE TERPROTEKSI
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth & Dashboard
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user()?->load('customer'));
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Modul Order & PDF
    Route::get('/orders/export/monthly', [OrderController::class, 'exportMonthlyReport']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::apiResource('orders', OrderController::class);

    // Modul Customer & History
    Route::get('/customers/{id}/orders', [CustomerController::class, 'orderHistory']);
    Route::get('/customers/{id}/payment-history', [CustomerController::class, 'paymentHistory']);
    Route::patch('/customers/{id}/pay-debt', [CustomerController::class, 'payDebt']);
    Route::apiResource('customers', CustomerController::class);

    // Modul Master Data
    Route::get('books/export/pdf', [BookController::class, 'exportPdf']);
    Route::patch('/books/{id}/restock', [BookController::class, 'restock']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('user-management', UserManagementController::class)->middleware('role:1,2');
});