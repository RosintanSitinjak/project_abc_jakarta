<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;

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

// =========================================================================
// 1. RUTE PUBLIK (Dapat Diakses Bebas Tanpa Login)
// =========================================================================
Route::prefix('public')->group(function () {
    Route::get('books', [BookController::class, 'index']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('articles', [PublicArticleController::class, 'index']);
});

// DOKUMEN CETAK RESMI (PDF FAKTUR & INVOICE)
// Ditaruh di publik agar bisa diunduh via tab baru tanpa kendala token
Route::get('/orders/{id}/invoice', [OrderController::class, 'downloadInvoice']);

// =========================================================================
// 2. RUTE AUTENTIKASI (LOGIN, REGISTER, & LUPA PASSWORD)
// =========================================================================
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']); 
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']); // Kirim link reset via email
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);     // Eksekusi password baru

// =========================================================================
// 3. RUTE TERPROTEKSI (Wajib Login - auth:sanctum)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // --- USER PROFILE & PASSWORD (MENDUKUNG HALAMAN PROFILE USER LALA) ---
    Route::get('/user', fn (Request $request) => $request->user()?->load('customer'));
    
    // Simpan Kontak & Alamat Pengiriman
    Route::put('/customer/profile', function (Request $request) {
        $user = $request->user();
        if ($user && $user->customer) {
            $user->customer->update($request->only(['phone', 'address']));
            return response()->json(['message' => 'Kontak dan alamat berhasil diperbarui.', 'customer' => $user->customer]);
        }
        return response()->json(['message' => 'Data profil pelanggan tidak ditemukan.'], 404);
    });

    // Ubah Password Mandiri User
    Route::put('/user/password', function (Request $request) {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password lama Anda tidak sesuai.'], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);
        return response()->json(['message' => 'Password berhasil diperbarui!']);
    });

    // --- DASHBOARD ADMIN & PIMPINAN ---
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- RUTE RESTOCK (Pengajuan ke Pak Andi & IPH) ---
    Route::get('/restock-requests', [RestockRequestController::class, 'index']);
    Route::post('/restock-requests', [RestockRequestController::class, 'store']);
    Route::patch('/restock-requests/{id}/status', [RestockRequestController::class, 'updateStatus']);
    Route::patch('/restock-requests/{id}/complete', [RestockRequestController::class, 'markAsCompleted']);

    // --- RUTE ATTACHMENT (UPLOAD GAMBAR) ---
    Route::post('/attachments', [AttachmentController::class, 'store']);
    Route::delete('/attachments/{id}', [AttachmentController::class, 'destroy']);

    // --- RUTE KHUSUS PELANGGAN & VERIFIKASI PL ---
    Route::get('/customers/{id}/orders', [CustomerController::class, 'orderHistory']);
    Route::get('/customers/{id}/payment-history', [CustomerController::class, 'paymentHistory']);
    Route::post('/customers/{id}/pay-debt', [CustomerController::class, 'payDebt']);
    Route::patch('/customers/{id}/approve', [CustomerController::class, 'approve']); 
    Route::patch('/customers/{id}/reject', [CustomerController::class, 'reject']);   
    Route::patch('/customers/{id}/toggle-status', [CustomerController::class, 'toggleStatus']); // SUSPEND / AKTIFKAN

    // --- RUTE KHUSUS PESANAN & LAPORAN ---
    Route::get('/orders/export/monthly', [OrderController::class, 'exportMonthlyReport']);
    Route::get('/orders/export', [OrderController::class, 'exportMonthlyReport']);
    Route::post('/orders/{id}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // --- RUTE EXPORT BUKU (PDF) ---
    Route::get('/books/export/pdf', [BookController::class, 'exportPdf']);

    // --- MODUL MASTER & TRANSAKSI ---
    Route::apiResource('orders', OrderController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('books', BookController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('articles', ArticleController::class);
    Route::apiResource('user-management', UserManagementController::class)->middleware('role:1,2');
});