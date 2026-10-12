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
     * Fungsi Login dengan Proteksi Status (Pending/Suspended)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $remember = (bool) ($credentials['remember'] ?? false);

        // 1. Cek Autentikasi Email & Password
        if (!Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ], $remember)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        // 2. AMBIL DATA USER SETELAH AUTH BERHASIL
        $user = Auth::user();

        // 3. LOGIKA PENGAMAN STATUS (PENTING!)
        // Cek apakah user ini memiliki profil customer (Pelanggan)
        if ($user->customer) {
            
            // Jika akun DIBLOKIR (Suspended)
            if ($user->customer->status === 'suspended') {
                Auth::guard('web')->logout(); // Keluarkan kembali dari session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Akun Anda telah dinonaktifkan. Silakan hubungi Admin ABC Jakarta.'],
                ]);
            }

            // Jika akun masih PENDING (Khusus Penginjil yang baru daftar)
            if ($user->customer->status === 'pending') {
                Auth::guard('web')->logout(); // Keluarkan kembali dari session
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Akun Anda sedang dalam proses verifikasi Admin. Mohon tunggu informasi selanjutnya.'],
                ]);
            }
        }

        // 4. JIKA LOLOS VALIDASI (STATUS AKTIF), REGENERATE SESSION
        $request->session()->regenerate();

        return response()->json($user->load('customer'));
    }

    /**
     * Registrasi Pelanggan Mandiri
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
            // 1. Buat User (Role 3 = Pelanggan)
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => Role::Pelanggan, 
            ]);

            // 2. Logika Status Awal & Limit Kredit
            $status = 'approved'; // Jemaat umum langsung aktif
            $limit  = 0;

            if ($request->type === 'penginjil') {
                $status = 'pending'; // Penginjil wajib dicek Intan (Admin) dulu
                $limit  = 5000000;   
            } elseif ($request->type === 'gereja' || $request->type === 'sekolah') {
                $limit  = 10000000;  
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
                    ? 'Pendaftaran berhasil. Akun Anda sedang diverifikasi oleh Admin.' 
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