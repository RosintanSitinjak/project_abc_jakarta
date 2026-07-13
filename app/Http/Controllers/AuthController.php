<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Enums\Role;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Fungsi Login (Sesuai Code Awal)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $remember = (bool) ($credentials['remember'] ?? false);

        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        $request->session()->regenerate();

        // Mengembalikan data user beserta detail pelanggannya jika ada
        return response()->json($request->user()->load('customer'));
    }

    /**
     * FITUR BARU: Registrasi Pelanggan Mandiri
     * Mendukung 4 tipe: jemaat, gereja, sekolah, penginjil
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'type'     => 'required|in:jemaat,gereja,sekolah,penginjil',
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Buat User (Otomatis Role Pelanggan / Angka 3)
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => Role::Pelanggan, // Pastikan di Enum Role::Pelanggan adalah 3
            ]);

            // 2. Logika Status & Limit Kredit berdasarkan Tipe
            $status = 'approved';
            $limit  = 0;

            if ($request->type === 'penginjil') {
                $status = 'pending'; // Wajib diverifikasi Admin Intan dulu
                $limit  = 5000000;   // Set plafon awal 5jt
            } elseif ($request->type === 'gereja' || $request->type === 'sekolah') {
                $limit  = 10000000;  // Contoh limit awal untuk institusi
            }

            // 3. Buat Data Detail Pelanggan
            Customer::create([
                'user_id'      => $user->id,
                'name'         => $request->name,
                'type'         => $request->type,
                'status'       => $status,
                'credit_limit' => $limit,
            ]);

            return response()->json([
                'message' => $request->type === 'penginjil' 
                    ? 'Pendaftaran berhasil. Mohon tunggu verifikasi Admin untuk mengaktifkan harga khusus PL.' 
                    : 'Pendaftaran berhasil! Silakan login.'
            ], 201);
        });
    }

    /**
     * Fungsi Logout
     */
    public function logout(): JsonResponse
    {
        Auth::guard('web')->logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return response()->json(['status' => 'logged_out']);
    }
}