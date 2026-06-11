<?php

use App\Events\HelloWorld;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\PublicProductController;
use App\Http\Controllers\PublicServiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ServiceScopeOfServiceController;
use App\Http\Controllers\SiteSettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return ['status' => 'ok'];
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('auth:sanctum')->patch('/user', [UserController::class, 'update']);

Route::middleware(['auth:sanctum', 'role:1'])->get('/admin/ping', function () {
    return ['ok' => true];
});

Route::post('/broadcast/hello', function (Request $request) {
    $message = (string) $request->input('message', 'Hello World');
    event(new HelloWorld($message));

    return [
        'status' => 'broadcasted',
        'message' => $message,
    ];
});

// =========================================================================
// RUTE PUBLIK UTAMA (Form Kontak)
// =========================================================================
// Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store'])->middleware('throttle:1,2');

// Route::post('/contact', function () {
//     return response()->json([
//         'success' => true,
//         'message' => 'Route reached'
//     ]);
// });

// Di routes/api.php
Route::post('/contact', [\App\Http\Controllers\ContactController::class, 'store']);

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);

Route::apiResource('attachments', AttachmentController::class);
Route::apiResource('site-settings', SiteSettingController::class);
Route::apiResource('services', ServiceController::class);
Route::apiResource('service-scopes', ServiceScopeOfServiceController::class)
    ->parameters(['service-scopes' => 'service_scope_of_service']);
Route::apiResource('categories', CategoryController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('portfolios', PortfolioController::class);
Route::apiResource('clients', ClientController::class);

Route::apiResource('user-management', \App\Http\Controllers\UserManagementController::class)
    ->middleware(['auth:sanctum', 'role:1,2']);

Route::middleware('auth:sanctum')->get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index']);

// =========================================================================
// GRUP RUTE PUBLIK WEBSITE
// =========================================================================
Route::prefix('public')->group(function () {
    Route::get('services', [PublicServiceController::class, 'index']);
    Route::get('products', [PublicProductController::class, 'index']);
    Route::get('portfolios', [PublicPortfolioController::class, 'index']);
    Route::get('articles', [\App\Http\Controllers\PublicArticleController::class, 'index']);
    Route::get('articles/popular', [\App\Http\Controllers\PublicArticleController::class, 'popular']);
    Route::get('articles/{slug}', [\App\Http\Controllers\PublicArticleController::class, 'show']);
    Route::post('visitor', [\App\Http\Controllers\VisitorController::class, 'store']);
});

// =========================================================================
// GRUP RUTE DASHBOARD ADMIN (PROTECTED SANCTUM)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard/articles', [\App\Http\Controllers\ArticleController::class, 'dashboardIndex']);
    
    Route::post('articles/upload-image', \App\Http\Controllers\ArticleImageController::class);
    Route::apiResource('articles', \App\Http\Controllers\ArticleController::class);
    Route::apiResource('contacts', \App\Http\Controllers\Admin\ContactController::class)->only(['index', 'show', 'update']);
});