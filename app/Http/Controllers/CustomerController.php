<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Enums\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();
        if ($request->filled('search')) $query->where('name', 'ILIKE', "%{$request->search}%");
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);
        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:jemaat,gereja,sekolah,penginjil',
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

    public function payDebt(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'attachment_id' => 'nullable|exists:attachments,id',
        ]);

        $customer = Customer::findOrFail($id);

        return DB::transaction(function () use ($customer, $request) {
            DB::table('debt_payments')->insert([
                'id' => Str::uuid(),
                'customer_id' => $customer->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'attachment_id' => $request->attachment_id,
                'created_at' => now(),
            ]);
            $customer->decrement('current_debt', $request->amount);
            return response()->json(['message' => 'Pembayaran piutang berhasil dicatat!']);
        });
    }

    public function paymentHistory($id): JsonResponse
    {
        $history = DB::table('debt_payments')
            ->leftJoin('attachments', 'debt_payments.attachment_id', '=', 'attachments.id')
            ->where('debt_payments.customer_id', $id)
            ->select('debt_payments.*', 'attachments.path as image_path')
            ->latest('debt_payments.created_at')
            ->get();

        $history->transform(function ($item) {
            $item->proof_url = $item->image_path ? asset('storage/' . $item->image_path) : null;
            return $item;
        });

        return response()->json($history);
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
            if ($user) $user->delete();
            return response()->json(['status' => 'deleted']);
        });
    }

    /**
     * LOGIKA VALIDASI LIMIT KREDIT (REVISI FINAL)
     */
    private function calculateValidatedLimit($type, $requestedLimit) {
        // 1. Jemaat: Wajib 0 (Cash Only)
        if ($type === 'jemaat') return 0;

        // 2. PL: Maksimal 5.000.000 (Kebijakan ABC Jakarta)
        if ($type === 'penginjil') {
            return ($requestedLimit > 5000000) ? 5000000 : ($requestedLimit ?? 5000000);
        }

        // 3. Gereja / Sekolah: Bebas. 
        // Jika Admin tidak isi (null), set angka 999 Juta sebagai tanda "Tanpa Batas"
        return $requestedLimit ?? 999000000;
    }
}