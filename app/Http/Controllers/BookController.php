<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\ManagesAttachments;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    use ManagesAttachments; 

    /**
     * Tampil Daftar Buku (Untuk Admin & Publik)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['category', 'thumbnail']);
        
        // Filter per Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Pencarian (Judul, Penulis, atau ISBN)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('author', 'ILIKE', "%{$search}%")
                  ->orWhere('isbn', 'ILIKE', "%{$search}%"); // Tambahan: Bisa cari via ISBN
            });
        }

        $books = $query->latest()->get();

        // Transformasi data untuk kebutuhan Frontend
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
            'author'      => 'nullable|string',
            'isbn'        => 'nullable|string|unique:books,isbn', // Unik jika diisi, boleh kosong
            'member_price'=> 'nullable|integer',
            'description' => 'nullable|string',
            'thumbnail_id'=> 'nullable',
        ]);

        // OTOMASI SLUG
        $data['slug'] = Str::slug($request->title) . '-' . rand(100, 999);

        $book = Book::create($data);

        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

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
            'author'      => 'nullable|string',
            // ISBN Unik kecuali untuk buku ini sendiri
            'isbn'        => ['nullable', 'string', Rule::unique('books', 'isbn')->ignore($book->id)],
            'member_price'=> 'nullable|integer',
            'description' => 'nullable|string',
            'thumbnail_id'=> 'nullable',
        ]);

        // Update Slug jika judul berubah (opsional, bisa dipertahankan slug lama jika mau)
        $data['slug'] = Str::slug($request->title) . '-' . rand(100, 999);

        $book->update($data);

        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

        if ($book->stock <= $book->rop_point) {
            $this->sendRestockNotification($book);
        }

        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * Logika Notifikasi ROP (Simulasi WhatsApp)
     */
    private function sendRestockNotification($book)
    {
        $pesan = "⚠️ *PERINGATAN STOK KRITIS*\n\n" .
                 "Buku: *{$book->title}*\n" .
                 "Sisa Stok: *{$book->stock}*\n" .
                 "Batas ROP: {$book->rop_point}\n\n" .
                 "Mohon segera lakukan restock.";

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

    public function exportPdf()
    {
        // Tambahkan ->with('category') agar nama kategori muncul di PDF
        $books = Book::with('category')->orderBy('title', 'asc')->get();

        $tanggalCetak = \Carbon\Carbon::now()->translatedFormat('d F Y H:i');

        $pdf = Pdf::loadView('reports.inventory_pdf', [
            'books' => $books,
            'tanggalCetak' => $tanggalCetak
        ]);

        // Kita buat layoutnya Landscape agar muat banyak kolom
        $pdf->setPaper('a4', 'landscape');

        $filename = 'Laporan-Stok-ABC-Jakarta-' . date('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }
}