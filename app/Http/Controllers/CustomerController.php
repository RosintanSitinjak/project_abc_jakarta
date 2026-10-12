<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:jemaat,gereja,sekolah,penginjil',
            'phone' => 'nullable|string',
            'credit_limit' => 'nullable|integer',
        ]);

        $limit = $this->calculateValidatedLimit($request->type, $request->credit_limit);

        return DB::transaction(function () use ($request, $limit) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => Str::slug($request->name) . rand(100, 999) . '@abc.com',
                'password' => Hash::make('12345678'),
                'role'     => Role::Pelanggan,
            ]);

            $customer = Customer::create([
                'user_id'      => $user->id,
                'name'         => $request->name,
                'type'         => $request->type,
                'status'       => ($request->type === 'penginjil') ? 'pending' : 'approved',
                'address'      => $request->address,
                'phone'        => $request->phone,
                'credit_limit' => $limit,
                'current_debt' => 0,
            ]);

            return response()->json($customer, 201);
        });
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:jemaat,gereja,sekolah,penginjil',
            'credit_limit' => 'nullable|integer',
        ]);

        $limit = $this->calculateValidatedLimit($request->type, $request->credit_limit);

        $customer->update([
            'name'         => $request->name,
            'type'         => $request->type,
            'status'       => $request->status ?? $customer->status,
            'address'      => $request->address,
            'phone'        => $request->phone,
            'credit_limit' => $limit,
        ]);

        return response()->json($customer);
    }

    public function approve($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->update(['status' => 'approved']);
        return response()->json(['message' => 'Akun Penginjil berhasil diaktifkan!']);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        return DB::transaction(function () use ($customer) {
            $user = $customer->user;
            $customer->delete();
            if ($user) { $user->delete(); }
            return response()->json(['status' => 'deleted']);
        });
    }

    private function calculateValidatedLimit($type, $requestedLimit)
    {
        if ($type === 'jemaat') return 0;
        if ($type === 'penginjil') {
            return ($requestedLimit > 5000000) ? 5000000 : ($requestedLimit ?? 0);
        }
        return $requestedLimit ?? 0;
    }
}