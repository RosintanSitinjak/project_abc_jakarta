<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesAttachments;
use App\Http\Requests\StoreProductRequest; // Sementara tetap pakai ini dulu agar tidak error
use App\Http\Requests\UpdateProductRequest; // Sementara tetap pakai ini dulu
use App\Models\Book; // SUDAH DIGANTI dari Product
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends Controller
{
    use ManagesAttachments;

    /**
     * Menampilkan daftar semua buku.
     */
    public function index(Request $request): JsonResponse
    {
        // Ambil data buku beserta kategori dan gambarnya
        $query = Book::query()
            ->with(['category', 'thumbnail']);

        // Fitur Pencarian berdasarkan Judul atau Penulis
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('author', 'ILIKE', "%{$search}%")
                  ->orWhere('isbn', 'ILIKE', "%{$search}%");
            });
        }

        // Fitur Urutkan (Sorting)
        if ($request->filled('sort_by')) {
            $sortOrder = $request->input('sort_order', 'asc');
            $sortBy = $request->sort_by;

            if ($sortBy === 'category.name') {
                $query->select('books.*')
                      ->leftJoin('categories', 'books.category_id', '=', 'categories.id')
                      ->orderBy('categories.name', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }
        } else {
            $query->latest();
        }

        // Fitur Paginate (Pembagian halaman)
        $books = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($books);
    }

    /**
     * Menyimpan buku baru (Input Admin).
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $book = Book::create($data); // Menggunakan model Book
        
        if ($thumbnailId) {
            $this->setSingleAttachment($book, 'thumbnail', $thumbnailId, 'thumbnail');
        }

        return response()->json($book->load(['category', 'thumbnail']), 201);
    }

    /**
     * Menampilkan detail satu buku.
     */
    public function show(Book $book): JsonResponse
    {
        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * Memperbarui data buku.
     */
    public function update(UpdateProductRequest $request, Book $book): JsonResponse
    {
        $data = $request->validated();
        $thumbnailId = $data['thumbnail_id'] ?? null;
        unset($data['thumbnail_id']);

        $book->update($data);
        
        if ($thumbnailId) {
            $this->setSingleAttachment($book, 'thumbnail', $thumbnailId, 'thumbnail');
        }

        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * Menghapus buku.
     */
    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['status' => 'deleted']);
    }
}