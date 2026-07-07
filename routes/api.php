<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleImageController;
use App\Http\Controllers\PublicArticleController;
use App\Http\Controllers\VisitorController;
use App\Http\Controllers\AttachmentController; // <--- PASTIKAN ADA INI

// --- RUTE PUBLIK (Tanpa Login) ---
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::post('visitor', [VisitorController::class, 'store']);
    Route::get('articles', [PublicArticleController::class, 'index']);
    Route::get('articles/{slug}', [PublicArticleController::class, 'show']);
});

// --- RUTE AUTHENTICATION ---
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

// --- RUTE MANAJEMEN (Wajib Login) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('orders', OrderController::class);

    Route::apiResource('articles', ArticleController::class);
    Route::post('articles/upload-image', ArticleImageController::class);

    Route::apiResource('user-management', \App\Http\Controllers\UserManagementController::class)
        ->middleware('role:1,2');

    Route::apiResource('attachments', AttachmentController::class);
});