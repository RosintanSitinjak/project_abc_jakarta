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
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }
        // Munculkan data terbaru
        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:umum,gereja,penginjil',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|integer',
        ]);

        // LOGIKA BISNIS: Validasi Limit berdasarkan tipe
        $limit = $this->validateCreditLimit($request->type, $request->credit_limit);

        return DB::transaction(function () use ($request, $limit) {
            // Buat akun user otomatis agar mereka bisa login nantinya
            $user = User::create([
                'name' => $request->name,
                'email' => str_replace(' ', '', strtolower($request->name)) . rand(10,99) . '@abc.com',
                'password' => Hash::make('12345678'),
                'role' => Role::Pelanggan,
            ]);

            $customer = Customer::create([
                'user_id' => $user->id,
                'name' => $request->name,
                'type' => $request->type,
                'address' => $request->address,
                'phone' => $request->phone,
                'credit_limit' => $limit,
            ]);

            return response()->json($customer, 201);
        });
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:umum,gereja,penginjil',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|integer',
        ]);

        $limit = $this->validateCreditLimit($request->type, $request->credit_limit);

        $customer->update([
            'name' => $request->name,
            'type' => $request->type,
            'address' => $request->address,
            'phone' => $request->phone,
            'credit_limit' => $limit,
        ]);

        return response()->json($customer);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['status' => 'deleted']);
    }

    // Fungsi Pembantu agar kodingan rapi (Don't Repeat Yourself)
    private function validateCreditLimit($type, $requestedLimit) {
        if ($type === 'umum') return 0;
        if ($type === 'penginjil' && $requestedLimit > 5000000) return 5000000;
        return $requestedLimit ?? 0;
    }
}