<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AttachmentController;

// =========================================================================
// 1. RUTE PUBLIK WEBSITE (Bisa diakses Karla tanpa Login)
// =========================================================================
Route::prefix('public')->group(function () {
    // Katalog Buku (Terdapat fitur Filter & Search di sini)
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    
    // Berita & Literasi
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
    
    // Tools
    Route::post('visitor', [VisitorController::class, 'store']);
});

// =========================================================================
// 2. RUTE AUTHENTICATION (Login / Logout)
// =========================================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

// =========================================================================
// 3. RUTE MANAJEMEN ADMIN (Wajib Login)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Info User Login & Dashboard
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Master Data & Transaksi
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);

    // Pengelolaan Konten
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('attachments', AttachmentController::class);

    // User Management (Khusus Admin/Owner)
    Route::apiResource('user-management', \App\Http\Controllers\UserManagementController::class)
        ->middleware('role:1,2');
});