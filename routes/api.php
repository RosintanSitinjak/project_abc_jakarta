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

// 1. RUTE PUBLIK
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
});

// 2. RUTE LOGIN
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']); 

// 3. RUTE TERPROTEKSI (Wajib Login)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', fn (Request $request) => $request->user()?->load('customer'));
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- RUTE RESTOCK (Taruh di atas apiResource agar prioritas) ---
    Route::get('/restock-requests', [RestockRequestController::class, 'index']);
    Route::post('/restock-requests', [RestockRequestController::class, 'store']);
    Route::patch('/restock-requests/{id}/status', [RestockRequestController::class, 'updateStatus']);
    Route::patch('/restock-requests/{id}/complete', [RestockRequestController::class, 'markAsCompleted']);

    // --- MODUL MASTER & TRANSAKSI ---
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('user-management', UserManagementController::class)->middleware('role:1,2');
});