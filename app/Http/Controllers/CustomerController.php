<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Models\Order;
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
        if ($request->filled('search')) $query->where('name', 'ILIKE', "%{$request->search}%");
        if ($request->filled('type')) $query->where('type', $request->type);
        if ($request->filled('status')) $query->where('status', $request->status);
        return response()->json($query->latest()->get());
    }

    public function orderHistory($id): JsonResponse
    {
        $orders = Order::where('customer_id', $id)->with(['items.book'])->latest()->get();
        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255', 
            'type' => 'required'
        ]);
        
        $limit = $this->calculateValidatedLimit($request->type, $request->credit_limit);
        
        return DB::transaction(function () use ($request, $limit) {
            $user = User::create([
                'name'     => $request->name, 
                'email'    => Str::slug($request->name) . rand(100, 999) . '@abc.com', 
                'password' => Hash::make('12345678'), 
                'role'     => Role::Pelanggan
            ]);
            
            $customer = Customer::create([
                'user_id'      => $user->id, 
                'name'         => $request->name, 
                'type'         => $request->type, 
                'status'       => ($request->type === 'penginjil') ? 'pending' : 'approved', 
                'address'      => $request->address, 
                'phone'        => $request->phone, 
                'credit_limit' => $limit, 
                'current_debt' => 0
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
            'credit_limit' => $limit
        ]);
        
        return response()->json($customer);
    }

    /**
     * Mengambil riwayat cicilan lengkap dengan URL Foto Bukti
     */
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

    public function payDebt(Request $request, $id): JsonResponse
    {
        $request->validate([
            'amount'         => 'required|integer', 
            'payment_method' => 'required', 
            'attachment_id'  => 'nullable'
        ]);
        
        $customer = Customer::findOrFail($id);
        
        return DB::transaction(function () use ($customer, $request) {
            DB::table('debt_payments')->insert([
                'id'             => Str::uuid(), 
                'customer_id'    => $customer->id, 
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method, 
                'attachment_id'  => $request->attachment_id,
                'created_at'     => now(), 
                'updated_at'     => now(),
            ]);
            
            $customer->decrement('current_debt', $request->amount);
            return response()->json(['message' => 'Pembayaran cicilan berhasil dicatat']);
        });
    }

    /**
     * SETUJUI AKUN PENGINJIL LITERATUR (PL)
     */
    public function approve($id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        
        $customer->update([
            'status'       => 'approved',
            'credit_limit' => ($customer->type === 'penginjil' && $customer->credit_limit <= 0) ? 5000000 : $customer->credit_limit,
        ]);

        return response()->json([
            'message'  => 'Akun Penginjil Literatur berhasil disetujui (AKTIF)!',
            'customer' => $customer
        ]);
    }

    /**
     * TOLAK AKUN PENGINJIL LITERATUR (PL)
     */
    public function reject(Request $request, $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $customer->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->input('reason', $request->input('rejection_reason', 'Pengajuan akun ditolak oleh Admin.')),
        ]);

        return response()->json([
            'message'  => 'Akun Penginjil Literatur telah ditolak.',
            'customer' => $customer
        ]);
    }

    private function calculateValidatedLimit($type, $requestedLimit) {
        if ($type === 'jemaat') return 0;
        if ($type === 'penginjil') return ($requestedLimit > 5000000) ? 5000000 : ($requestedLimit ?? 5000000);
        return $requestedLimit ?? 999000000;
    }

    /**
     * HAPUS PELANGGAN BERPROTEKSI KEUANGAN
     */
    public function destroy(Customer $customer): JsonResponse
    {
        // 1. CEK: Apakah pelanggan masih punya sisa piutang aktif?
        if ($customer->current_debt > 0) {
            return response()->json([
                'message' => "Gagal menghapus! Pelanggan '{$customer->name}' masih memiliki sisa piutang sebesar Rp " . number_format($customer->current_debt, 0, ',', '.') . ". Harap selesaikan pelunasan piutang terlebih dahulu."
            ], 422);
        }

        // 2. CEK: Apakah masih ada pesanan yang belum berstatus paid/lunas?
        if ($customer->orders()->where('payment_status', '!=', 'paid')->exists()) {
            return response()->json([
                'message' => "Gagal menghapus! Pelanggan '{$customer->name}' masih memiliki transaksi pesanan yang belum lunas."
            ], 422);
        }

        return DB::transaction(function () use ($customer) {
            $user = $customer->user;
            $customer->delete(); 
            if ($user) $user->delete();
            return response()->json(['status' => 'deleted', 'message' => 'Data pelanggan berhasil dihapus.']);
        });
    }
}