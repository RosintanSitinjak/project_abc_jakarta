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
    public function index(Request $request): JsonResponse
    {
        $query = User::with('customer');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->whereHas('customer', fn($q) => $q->where('type', $request->type));
        }

        if ($request->filled('status')) {
            $query->whereHas('customer', fn($q) => $q->where('status', $request->status));
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
            // Validasi tambahan: Jika role = 3 (Pelanggan), maka customer_type wajib diisi
            'customer_type' => ['required_if:role,3', 'nullable', 'in:jemaat,penginjil,gereja,sekolah'],
        ]);

        return DB::transaction(function () use ($request, $data) {
            $data['password'] = Hash::make($data['password']);
            $user = User::create($data);

            // JIKA ROLE ADALAH PELANGGAN (3), BUATKAN PROFIL CUSTOMER NYA
            if ($request->role == 3) {
                $limit = 0;
                if ($request->customer_type === 'penginjil') $limit = 5000000;
                if ($request->customer_type === 'gereja' || $request->customer_type === 'sekolah') $limit = 10000000;

                $user->customer()->create([
                    'name' => $request->name,
                    'type' => $request->customer_type,
                    'status' => 'approved', // Karena dibuat admin, otomatis aktif
                    'credit_limit' => $limit,
                    'current_debt' => 0
                ]);
            }

            return response()->json($user->load('customer'), 201);
        });
    }

    public function update(Request $request, User $user_management): JsonResponse
    {
        if ($request->user()->role !== Role::Owner && $user_management->role === Role::Owner) {
            return response()->json(['message' => 'Dilarang mengubah data Pimpinan.'], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user_management->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'integer'],
            'customer_type' => ['required_if:role,3', 'nullable', 'in:jemaat,penginjil,gereja,sekolah'],
        ]);

        return DB::transaction(function () use ($request, $data, $user_management) {
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user_management->update($data);

            // Sinkronisasi tipe di tabel customers jika dia pelanggan
            if ($user_management->customer) {
                $user_management->customer->update([
                    'name' => $data['name'],
                    'type' => $request->customer_type ?? $user_management->customer->type
                ]);
            }

            return response()->json($user_management->fresh()->load('customer'));
        });
    }

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

    public function approve($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'approved', 'rejection_reason' => null]);
        return response()->json(['message' => 'Akun PL berhasil disetujui.']);
    }

    public function reject(Request $request, $id): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'rejected', 'rejection_reason' => $request->reason, 'type' => 'jemaat']);
        return response()->json(['message' => 'Pendaftaran PL ditolak.']);
    }

    public function toggleStatus($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $newStatus = ($customer->status === 'suspended') ? 'approved' : 'suspended';
        $customer->update(['status' => $newStatus]);
        return response()->json(['message' => 'Status akses akun berhasil diubah']);
    }
}