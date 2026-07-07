<?php

namespace App\Http\Controllers;

// --- SEMUA IMPORT WAJIB DI SINI (DI LUAR CLASS) ---
use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Concerns\ManagesAttachments; // Import alamat Trait yang benar
// --------------------------------------------------

class BookController extends Controller
{
    // Memanggil Trait untuk urusan gambar/file
    use ManagesAttachments; 

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

        // --- LOGIKA ROP SAAT INPUT BARU ---
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

        // --- LOGIKA ROP SAAT UPDATE DATA ---
        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book);
    }

    private function sendRestockNotification($book)
    {
        $adminPhone = '6281376990897'; 
        $pesan = "⚠️ *PERINGATAN STOK KRITIS*\n\n" .
                 "Buku: *{$book->title}*\n" .
                 "Sisa Stok: *{$book->stock}*\n" .
                 "Batas ROP: {$book->rop_point}\n\n" .
                 "Mohon segera lakukan pengajuan restock ke IPH.";

        // Ini akan mencatat di storage/logs/laravel.log
        Log::info("WA Terkirim ke Admin: " . $pesan);
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['status' => 'deleted']);
    }
}