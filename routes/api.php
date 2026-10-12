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
use App\Http\Controllers\RestockRequestController;
use App\Http\Controllers\AttachmentController;

// 1. RUTE PUBLIK
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
});

// 2. RUTE LOGIN & REGISTER
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']); 

// 3. RUTE TERPROTEKSI (Wajib Login)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', fn (Request $request) => $request->user()?->load('customer'));
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- RUTE RESTOCK (Pengajuan ke Pak Andi & IPH) ---
    Route::get('/restock-requests', [RestockRequestController::class, 'index']);
    Route::post('/restock-requests', [RestockRequestController::class, 'store']);
    Route::patch('/restock-requests/{id}/status', [RestockRequestController::class, 'updateStatus']);
    Route::patch('/restock-requests/{id}/complete', [RestockRequestController::class, 'markAsCompleted']);

    // --- RUTE ATTACHMENT (UPLOAD GAMBAR) ---
    Route::post('/attachments', [AttachmentController::class, 'store']);
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy']);

    // --- RUTE KHUSUS PELANGGAN (RIWAYAT, PIUTANG & VERIFIKASI PL) ---
    Route::get('/customers/{id}/orders', [CustomerController::class, 'orderHistory']);
    Route::get('/customers/{id}/payment-history', [CustomerController::class, 'paymentHistory']);
    Route::post('/customers/{id}/pay-debt', [CustomerController::class, 'payDebt']);
    Route::patch('/customers/{id}/approve', [CustomerController::class, 'approve']); // <-- SETUJUI PL
    Route::patch('/customers/{id}/reject', [CustomerController::class, 'reject']);   // <-- TOLAK PL

    // --- RUTE KHUSUS PESANAN (INVOICE & LAPORAN TERFILTER) ---
    Route::get('/orders/export/monthly', [OrderController::class, 'exportMonthlyReport']);
    Route::get('/orders/export', [OrderController::class, 'exportMonthlyReport']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // --- RUTE KHUSUS BUKU (EXPORT PDF) ---
    Route::get('/books/export/pdf', [BookController::class, 'exportPdf']);

    // --- MODUL MASTER & TRANSAKSI ---
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('user-management', UserManagementController::class)->middleware('role:1,2');
});