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
    /**
     * Menampilkan daftar semua pengajuan restock (untuk Admin & Pak Andi)
     */
    public function index(): JsonResponse
    {
        $requests = RestockRequest::with(['book', 'creator'])->latest()->get();
        return response()->json($requests);
    }

    /**
     * 1. Admin Mengajukan Penambahan Stok Buku
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'book_id'  => 'required|exists:books,id',
            'quantity' => 'required|integer|min:1',
            'note'     => 'nullable|string'
        ]);

        $restock = RestockRequest::create([
            'id'             => (string) Str::uuid(),
            'book_id'        => $request->book_id,
            'user_id'        => auth()->id(),          // Sesuai kolom database
            'quantity'       => $request->quantity,    // Sesuai kolom database
            'status'         => 'pending',
            'pimpinan_notes' => null,
            'is_completed'   => false,
        ]);

        return response()->json([
            'message' => 'Pengajuan stok berhasil dikirim ke Pak Andi!',
            'data'    => $restock->load(['book', 'creator'])
        ], 201);
    }

    /**
     * 2. Keputusan Pak Andi: Menyetujui (ACC) atau Menolak (Reject)
     * Komentar bersifat opsional (bisa diisi catatan/alasan, bisa juga dikosongkan)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'notes'  => 'nullable|string' // Bisa diisi catatan atau dibiarkan kosong
        ]);

        $restock = RestockRequest::findOrFail($id);
        
        $restock->update([
            'status'         => $request->status,
            'pimpinan_notes' => $request->notes, // Menyimpan komentar Pak Andi
        ]);

        $statusText = $request->status === 'approved' ? 'disetujui (ACC)' : 'ditolak';

        return response()->json([
            'message' => "Pengajuan restock berhasil {$statusText} oleh Pak Andi."
        ]);
    }

    /**
     * 3. Barang dari IPH Tiba di ABC Jakarta: Tambahkan Stok Otomatis
     */
    public function markAsCompleted($id): JsonResponse
    {
        $restock = RestockRequest::findOrFail($id);

        if ($restock->status !== 'approved') {
            return response()->json([
                'message' => 'Barang hanya bisa diselesaikan jika sudah disetujui (ACC) oleh Pak Andi.'
            ], 422);
        }

        if ($restock->is_completed) {
            return response()->json([
                'message' => 'Pengajuan restock ini sudah pernah diselesaikan sebelumnya.'
            ], 422);
        }

        return DB::transaction(function () use ($restock) {
            $book = Book::find($restock->book_id);
            if ($book) {
                // Tambah stok buku sesuai quantity yang diajukan
                $book->increment('stock', $restock->quantity);
            }

            $restock->update([
                'is_completed' => true,
                'completed_at' => now()
            ]);

            return response()->json([
                'message'   => "Buku dari IPH berhasil diterima! Stok {$book?->title} otomatis bertambah {$restock->quantity} pcs di katalog."
            ]);
        });
    }

    public function destroy($id): JsonResponse
    {
        $restock = RestockRequest::findOrFail($id);
        $restock->delete();
        return response()->json(['status' => 'deleted']);
    }
}