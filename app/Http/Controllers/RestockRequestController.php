<?php

namespace App\Http\Controllers;

use App\Models\RestockRequest;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestockRequestController extends Controller
{
    public function index(): JsonResponse
    {
        // Load relasi book dan creator (user yang buat pengajuan)
        $requests = RestockRequest::with(['book', 'creator'])->latest()->get();
        return response()->json($requests);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'note' => 'nullable|string'
        ]);

        $restock = RestockRequest::create([
            'id' => (string) Str::uuid(),
            'book_id' => $request->book_id,
            'qty_requested' => $request->quantity, // Sesuai database
            'admin_note' => $request->note,       // Sesuai database
            'created_by' => auth()->id(),          // Sesuai database
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Pengajuan stok berhasil dikirim!'], 201);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string'
        ]);

        $restock = RestockRequest::findOrFail($id);
        
        $restock->update([
            'status' => $request->status,
            'leader_comment' => $request->notes, // Sesuai database
            'approved_by' => auth()->id(),       // Pak Andi yang approve
            'qty_approved' => $restock->qty_requested // Set default approve sama dengan request
        ]);

        return response()->json(['message' => 'Tanggapan Pimpinan berhasil disimpan.']);
    }

    public function markAsCompleted($id): JsonResponse
    {
        $restock = RestockRequest::findOrFail($id);

        if ($restock->status !== 'approved') {
            return response()->json(['message' => 'Barang hanya bisa diterima jika sudah disetujui.'], 422);
        }

        return DB::transaction(function () use ($restock) {
            $book = Book::find($restock->book_id);
            if ($book) {
                // Tambah stok berdasarkan jumlah yang disetujui pimpinan
                $book->increment('stock', $restock->qty_approved);
            }

            $restock->update([
                'is_completed' => true,
                'completed_at' => now()
            ]);

            return response()->json(['message' => 'Stok buku resmi diperbarui di gudang!']);
        });
    }

    public function destroy($id): JsonResponse
    {
        $restock = RestockRequest::findOrFail($id);
        $restock->delete();
        return response()->json(['status' => 'deleted']);
    }
}