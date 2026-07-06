<?php

use App\Events\HelloWorld;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController; // Sudah diperbarui dari ClientController
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;      // Sudah diperbarui dari ProductController
use App\Http\Controllers\OrderController;     // Controller baru untuk pesanan
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- System Health Check ---
Route::get('/health', function () {
    return ['status' => 'ok'];
});

// --- Authentication Routes ---
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

// --- Authenticated User Profile ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::patch('/user', [UserController::class, 'update']);
});

// =========================================================================
// RUTE MASTER DATA & MANAJEMEN (ADMIN & PIMPINAN)
// =========================================================================
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Dashboard Stats
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Manajemen User (Hanya Role 1 & 2)
    Route::apiResource('user-management', UserManagementController::class)
        ->middleware('role:1,2');

    // Master Data Literasi & Pelanggan
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('customers', CustomerController::class);
    
    // Transaksi Pesanan (Penjualan, Pembayaran, Pengiriman)
    Route::apiResource('orders', OrderController::class);

    // Blog / Literasi Info
    Route::apiResource('articles', ArticleController::class);
    Route::post('articles/upload-image', ArticleImageController::class);
    Route::get('dashboard/articles', [ArticleController::class, 'dashboardIndex']);

    // System Utils
    Route::apiResource('attachments', AttachmentController::class);
    Route::apiResource('site-settings', SiteSettingController::class);
});

// =========================================================================
// RUTE PUBLIK WEBSITE (Tanpa Login)
// =========================================================================
Route::prefix('public')->group(function () {
    // Pengunjung bisa melihat katalog dan artikel
    Route::get('categories', [CategoryController::class, 'index']); // Versi publik
    Route::get('books', [BookController::class, 'index']);           // Versi publik
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/popular', [PublicArticleController::class, 'popular']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
    
    // Tracking pengunjung & Form Kontak
    Route::post('visitor', [VisitorController::class, 'store']);
    Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store']);
});

// --- Development/Test Routes ---
Route::post('/broadcast/hello', function (Request $request) {
    $message = (string) $request->input('message', 'Hello World');
    event(new HelloWorld($message));
    return ['status' => 'broadcasted', 'message' => $message];
});