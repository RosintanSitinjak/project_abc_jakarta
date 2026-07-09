<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\ManagesAttachments;

class BookController extends Controller
{
    use ManagesAttachments; 

    /**
     * Tampil Daftar Buku (Untuk Admin & Publik)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['category', 'thumbnail']);
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('author', 'ILIKE', "%{$search}%");
            });
        }

        $books = $query->latest()->get();

        // Memberikan URL Gambar Lengkap untuk Karla (User)
        $books->transform(function ($book) {
            $book->image_url = $book->thumbnail ? asset('storage/' . $book->thumbnail->path) : null;
            return $book;
        });

        return response()->json($books);
    }

    /**
     * Simpan Buku Baru
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'price'       => 'required|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
            'author'      => 'nullable',
            'isbn'        => 'nullable',
            'member_price'=> 'nullable',
            'description' => 'nullable',
            'thumbnail_id'=> 'nullable',
        ]);

        // OTOMASI SLUG: Agar link tidak undefined di sisi Karla
        $data['slug'] = Str::slug($request->title) . '-' . rand(100, 999);

        $book = Book::create($data);

        // Menghubungkan Gambar
        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

        // Cek Peringatan Stok (ROP)
        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book->load(['category', 'thumbnail']), 201);
    }

    /**
     * Detail Buku
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * Update Data Buku
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'price'       => 'required|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
            'author'      => 'nullable',
            'isbn'        => 'nullable',
            'member_price'=> 'nullable',
            'description' => 'nullable',
            'thumbnail_id'=> 'nullable',
        ]);

        // Update Slug jika judul berubah
        $data['slug'] = Str::slug($request->title) . '-' . rand(100, 999);

        $book->update($data);

        // Update Gambar
        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

        // Cek Peringatan Stok (ROP)
        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * Logika Notifikasi ROP
     */
    private function sendRestockNotification($book)
    {
        $adminPhone = '6281376990897'; 
        $pesan = "⚠️ *PERINGATAN STOK KRITIS*\n\n" .
                 "Buku: *{$book->title}*\n" .
                 "Sisa Stok: *{$book->stock}*\n" .
                 "Batas ROP: {$book->rop_point}\n\n" .
                 "Mohon segera lakukan pengajuan restock ke IPH.";

        Log::info("WA Terkirim ke Admin: " . $pesan);
    }

    /**
     * Hapus Buku
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['status' => 'deleted']);
    }
}