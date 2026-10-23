<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Enums\Role;
use App\Http\Requests\LoginRequest;
use App\Mail\WelcomeRegistrationMail;
use App\Mail\ResetPasswordMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
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

        // 2. Ambil data user setelah autentikasi berhasil
        $user = Auth::user();

        // 3. Logika Pengaman Status Akun Pelanggan
        if ($user->customer) {
            
            // Jika akun DIBLOKIR / SUSPENDED
            if ($user->customer->status === 'suspended') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Akun Anda telah dinonaktifkan oleh Admin ABC Jakarta. Silakan hubungi kantor pelayanan.'],
                ]);
            }

            // Jika akun masih PENDING (Khusus Penginjil Literatur baru)
            if ($user->customer->status === 'pending') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Akun Penginjil Literatur Anda sedang dalam proses verifikasi Admin ABC Jakarta. Mohon tunggu informasi selanjutnya.'],
                ]);
            }
        }

        // 4. Jika lolos validasi (Status Aktif), regenerate session
        $request->session()->regenerate();

        return response()->json($user->load('customer'));
    }

    /**
     * Registrasi Pelanggan Mandiri + Kirim Email Otomatis
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'type'     => 'required|in:jemaat,gereja,sekolah,penginjil',
        ]);

        $createdData = DB::transaction(function () use ($request) {
            // 1. Buat Akun User
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => Role::Pelanggan, 
            ]);

            // 2. Logika Status Awal & Limit Plafon Kredit
            $status = 'approved'; // Jemaat umum langsung aktif
            $limit  = 0;

            if ($request->type === 'penginjil') {
                $status = 'pending'; // Penginjil wajib verifikasi admin
                $limit  = 5000000;   // Plafon maksimal Rp 5.000.000
            } elseif ($request->type === 'gereja' || $request->type === 'sekolah') {
                $limit  = 10000000;  
            }

            // 3. Buat Data Profil Pelanggan
            $customer = Customer::create([
                'user_id'      => $user->id,
                'name'         => $request->name,
                'phone'        => $request->phone,
                'type'         => $request->type,
                'status'       => $status,
                'credit_limit' => $limit,
            ]);

            return [
                'user'     => $user,
                'customer' => $customer
            ];
        });

        // 4. Kirim Email Resmi ke Alamat Email Pendaftar
        try {
            Mail::to($createdData['user']->email)->send(
                new WelcomeRegistrationMail($createdData['user'], $createdData['customer'])
            );
        } catch (\Throwable $e) {
            Log::error('Gagal mengirimkan email registrasi: ' . $e->getMessage());
        }

        return response()->json([
            'message' => $request->type === 'penginjil' 
                ? 'Pendaftaran berhasil. Akun Anda sedang diverifikasi Admin. Rincian pendaftaran telah dikirim ke email Anda.' 
                : 'Pendaftaran berhasil! Konfirmasi akun telah dikirim ke email Anda. Silakan login.'
        ], 201);
    }

    /**
     * REQUEST LUPA PASSWORD (Kirim Link Reset via Email)
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'Alamat email tidak terdaftar dalam sistem ABC Jakarta.'
        ]);

        $user = User::where('email', $request->email)->first();
        $token = Str::random(60);

        // Simpan / update token di tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token'      => Hash::make($token), 
                'created_at' => now()
            ]
        );

        // Kirim email reset password
        try {
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
        } catch (\Throwable $e) {
            Log::error('Gagal mengirim email reset password: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim email reset password. Periksa koneksi internet.'], 500);
        }

        return response()->json(['message' => 'Tautan atur ulang kata sandi telah dikirim ke email Anda.']);
    }

    /**
     * EKSEKUSI RESET PASSWORD DENGAN TOKEN
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Tautan reset password tidak valid atau telah kedaluwarsa.'], 422);
        }

        // Update password baru di tabel users
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Hapus token yang sudah dipakai
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.']);
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