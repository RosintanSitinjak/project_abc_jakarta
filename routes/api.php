<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- IMPORT SEMUA CONTROLLER ---
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\ContactController;

// =========================================================================
// 1. RUTE PUBLIK WEBSITE (Bisa diakses Tanpa Login)
// =========================================================================
Route::prefix('public')->group(function () {
    // Katalog & Literasi untuk Jemaat
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
    
    // Fitur Tracker & Kontak
    Route::post('visitor', [VisitorController::class, 'store']);
    Route::post('contact', [ContactController::class, 'store']);
});

// =========================================================================
// 2. RUTE AUTENTIKASI (Pintu Masuk & Daftar)
// =========================================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']); // API untuk Karla (Sign Up)

// =========================================================================
// 3. RUTE TERPROTEKSI (Wajib Login / Sanctum)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Keluar dari sistem
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Data Profile User & Statistik Dashboard
    Route::get('/user', fn (Request $request) => $request->user()->load('customer'));
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- MODUL MASTER DATA & TRANSAKSI ---
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);

    // --- MODUL KONTEN & ASSET ---
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('attachments', AttachmentController::class);
    Route::apiResource('site-settings', SiteSettingController::class);

    // --- FITUR KHUSUS ADMIN / PIMPINAN ---
    
    // Manajemen User (Akses terbatas Role 1 & 2)
    Route::apiResource('user-management', UserManagementController::class)
        ->middleware('role:1,2');

    // Verifikasi Akun Penginjil Literatur (PL)
    Route::patch('/customers/{id}/approve', [CustomerController::class, 'approve']);
});

// --- System Check ---
Route::get('/health', function () {
    return ['status' => 'ok', 'message' => 'Sistem Literasi ABC Jakarta Aktif'];
});