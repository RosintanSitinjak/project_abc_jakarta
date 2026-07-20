<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\ContactController;

// =========================================================================
// 1. RUTE PUBLIK WEBSITE (JALUR LALA / USER)
// =========================================================================
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
    Route::post('visitor', [VisitorController::class, 'store']);
    Route::post('contact', [ContactController::class, 'store']);
});

// =========================================================================
// 2. RUTE AUTENTIKASI
// =========================================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']); 

// =========================================================================
// 3. RUTE TERPROTEKSI (JALUR INTAN / ADMIN)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user()?->load('customer'));
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- MODUL MASTER DATA ---
    Route::apiResource('categories', CategoryController::class);
    Route::get('books/export/pdf', [BookController::class, 'exportPdf']);
    Route::apiResource('books', BookController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('articles', ArticleController::class); 
    Route::apiResource('attachments', AttachmentController::class);
    Route::apiResource('site-settings', SiteSettingController::class);

    // --- MANAJEMEN USER & ROLE (Role 1: Owner, 2: Admin) ---
    Route::apiResource('user-management', UserManagementController::class)
        ->middleware('role:1,2');

    // FITUR APPROVAL, REJECT, & SUSPEND (Tambahan Baru)
    // Saya arahkan semua ke UserManagementController agar rapi di satu tempat
    Route::patch('/customers/{id}/approve', [UserManagementController::class, 'approve']);
    Route::patch('/customers/{id}/reject', [UserManagementController::class, 'reject']);
    Route::patch('/customers/{id}/toggle-status', [UserManagementController::class, 'toggleStatus']);
});

// --- System Check ---
Route::get('/health', fn () => ['status' => 'ok', 'message' => 'Sistem ABC Jakarta Aktif', 'time' => now()]);