<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Tampil Daftar Pelanggan (Untuk menu Gereja & Jemaat)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }
        return response()->json($query->latest()->get());
    }

    /**
     * Simpan Pelanggan Baru (Input Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:jemaat,gereja,sekolah,penginjil',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|integer',
        ]);

        return DB::transaction(function () use ($request) {
            // Buat akun login otomatis
            $user = User::create([
                'name'     => $request->name,
                'email'    => str_replace(' ', '', strtolower($request->name)) . rand(10,99) . '@abc.com',
                'password' => Hash::make('12345678'),
                'role'     => Role::Pelanggan,
            ]);

            // Buat detail pelanggan
            $customer = Customer::create([
                'user_id'      => $user->id,
                'name'         => $request->name,
                'type'         => $request->type,
                'status'       => ($request->type === 'penginjil') ? 'pending' : 'approved',
                'address'      => $request->address,
                'phone'        => $request->phone,
                'credit_limit' => $request->credit_limit ?? 0,
            ]);

            return response()->json($customer, 201);
        });
    }

    /**
     * Update Data Pelanggan
     */
    public function update(Request $request, Customer $customer): JsonResponse
    {
        $customer->update($request->all());
        return response()->json($customer);
    }

    /**
     * FITUR VERIFIKASI PL (Sesuai Skripsi)
     * Mengubah status PENDING menjadi APPROVED
     */
    public function approve($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'status' => 'approved'
        ]);

        return response()->json(['message' => 'Akun Penginjil berhasil diaktifkan!']);
    }

    /**
     * Hapus Pelanggan
     */
    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['status' => 'deleted']);
    }
}