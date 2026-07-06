<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // WAJIB TAMBAHKAN INI
use Illuminate\Support\Facades\Http;

class BookController extends Controller
{
    use Concerns\ManagesAttachments;

    public function index(Request $request): JsonResponse
    {
        $query = Book::with('category');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('author', 'ILIKE', "%{$search}%");
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'author'      => 'nullable|string',
            'isbn'        => 'nullable|string',
            'price'       => 'required|integer',
            'member_price'=> 'nullable|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
            'description' => 'nullable|string',
        ]);

        $book = Book::create($data);

        // --- CEK ROP SAAT INPUT BARU ---
        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book, 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required',
            'title'       => 'required',
            'price'       => 'required|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
        ]);

        $book->update($request->all());

        // --- CEK ROP SAAT UPDATE DATA ---
        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book);
    }

// Pastikan ada ini di paling atas file
use Illuminate\Support\Facades\Log;

private function sendRestockNotification($book)
{
    $adminPhone = '6281376990897'; 
    $pesan = "⚠️ *PERINGATAN STOK KRITIS*\n" .
             "Buku: {$book->title}\n" .
             "Sisa: {$book->stock}\n" .
             "Segera hubungi IPH untuk restock.";

    // INI PENGGANTI WHATSAPP UNTUK SEMENTARA
    Log::info("TRIGGER WHATSAPP: Mengirim pesan ke $adminPhone. Isi pesan: $pesan");
}
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['status' => 'deleted']);
    }
}