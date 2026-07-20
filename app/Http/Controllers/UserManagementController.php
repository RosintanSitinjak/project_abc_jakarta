<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Tampil Daftar Pengguna dengan Filter
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::with('customer');

        // Filter Pencarian (Nama/Email)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        // Filter Tipe Akun (jemaat, penginjil, gereja, sekolah)
        if ($request->filled('type')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('type', $request->type);
            });
        }

        // Filter Status (pending, approved, rejected, suspended)
        if ($request->filled('status')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('status', $request->status);
            });
        }

        return response()->json($query->latest()->get());
    }

    /**
     * Simpan User Baru (Manual oleh Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
        ]);

        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);

            // Jika yang dibuat adalah Pelanggan (Role 3), buatkan profil customer-nya juga
            if ($data['role'] == 3) {
                $user->customer()->create([
                    'name' => $data['name'],
                    'type' => 'jemaat', // Default admin create as jemaat
                    'status' => 'approved'
                ]);
            }

            return response()->json($user->load('customer'), 201);
        });
    }

    /**
     * Update Data User
     */
    public function update(Request $request, User $user_management): JsonResponse
    {
        // Proteksi agar Admin Staff tidak bisa ubah data Owner
        if ($request->user()->role !== Role::Owner && $user_management->role === Role::Owner) {
            return response()->json(['message' => 'Dilarang mengubah data Pimpinan.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user_management->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user_management->update($data);

        // Sinkronisasi nama di tabel customer jika ada
        if ($user_management->customer) {
            $user_management->customer->update(['name' => $data['name']]);
        }

        return response()->json($user_management->fresh()->load('customer'));
    }

    /**
     * Hapus User & Data Pelanggannya
     */
    public function destroy(Request $request, User $user_management): JsonResponse
    {
        if ($request->user()->role !== Role::Owner && $user_management->role === Role::Owner) {
            return response()->json(['message' => 'Dilarang menghapus data Pimpinan.'], 403);
        }

        return DB::transaction(function () use ($user_management) {
            if ($user_management->customer) {
                $user_management->customer->delete();
            }
            $user_management->delete();
            return response()->json(['status' => 'deleted']);
        });
    }

    /**
     * FUNGSI KHUSUS: SETUJUI PL
     */
    public function approve($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update([
            'status' => 'approved', 
            'rejection_reason' => null
        ]);
        return response()->json(['message' => 'Akun PL berhasil disetujui.']);
    }

    /**
     * FUNGSI KHUSUS: TOLAK PL (Turun ke Jemaat Umum)
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'type' => 'jemaat' // Otomatis jadi jemaat biasa agar tidak bisa akses harga PL
        ]);
        
        return response()->json(['message' => 'Pendaftaran PL ditolak. Status user tetap aktif sebagai Jemaat Umum.']);
    }

    /**
     * FUNGSI KHUSUS: SUSPEND / AKTIFKAN AKUN
     */
    public function toggleStatus($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $newStatus = ($customer->status === 'suspended') ? 'approved' : 'suspended';
        $customer->update(['status' => $newStatus]);
        
        return response()->json([
            'message' => $newStatus === 'suspended' ? 'Akun berhasil diblokir.' : 'Akun berhasil diaktifkan kembali.'
        ]);
    }
}