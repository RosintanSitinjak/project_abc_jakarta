<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\ManagesAttachments;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    use ManagesAttachments; 

    public function index(Request $request): JsonResponse
    {
        $query = Book::with(['category', 'thumbnail']);
        
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // --- FITUR BARU: Filter Stok Kritis ---
        if ($request->low_stock === 'true') {
            $query->whereColumn('stock', '<=', 'rop_point');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ILIKE', "%{$search}%")
                  ->orWhere('author', 'ILIKE', "%{$search}%")
                  ->orWhere('isbn', 'ILIKE', "%{$search}%");
            });
        }

        $books = $query->latest()->get();

        $books->transform(function ($book) {
            $book->image_url = $book->thumbnail ? asset('storage/' . $book->thumbnail->path) : null;
            return $book;
        });

        return response()->json($books);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'price'       => 'required|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
            'author'      => 'nullable|string',
            'isbn'        => 'nullable|string|unique:books,isbn', // Boleh kosong, unik jika diisi
            'member_price'=> 'nullable|integer',
            'description' => 'nullable|string',
            'thumbnail_id'=> 'nullable',
        ]);

        $data['slug'] = Str::slug($request->title) . '-' . rand(100, 999);
        $book = Book::create($data);

        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

        return response()->json($book->load(['category', 'thumbnail']), 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title'       => 'required|string|max:255',
            'price'       => 'required|integer',
            'stock'       => 'required|integer',
            'rop_point'   => 'required|integer',
            'author'      => 'nullable|string',
            'isbn'        => ['nullable', 'string', Rule::unique('books', 'isbn')->ignore($book->id)],
            'member_price'=> 'nullable|integer',
            'description' => 'nullable|string',
            'thumbnail_id'=> 'nullable',
        ]);

        $book->update($data);

        if ($request->filled('thumbnail_id')) {
            $this->setSingleAttachment($book, 'thumbnail', $request->thumbnail_id, 'thumbnail');
        }

        return response()->json($book->load(['category', 'thumbnail']));
    }

    /**
     * FITUR BARU: Tambah Stok Instan (Restock)
     */
    public function restock(Request $request, $id): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1']);
        $book = Book::findOrFail($id);
        $book->increment('stock', $request->quantity);

        return response()->json(['message' => "Stok {$book->title} berhasil ditambah!", 'new_stock' => $book->stock]);
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();
        return response()->json(['status' => 'deleted']);
    }

    public function exportPdf()
    {
        $books = Book::with('category')->orderBy('title', 'asc')->get();
        $tanggalCetak = \Carbon\Carbon::now()->translatedFormat('d F Y H:i');
        $pdf = Pdf::loadView('reports.inventory_pdf', ['books' => $books, 'tanggalCetak' => $tanggalCetak]);
        $pdf->setPaper('a4', 'landscape');
        return $pdf->download('Laporan-Stok-ABC-Jakarta-' . date('Y-m-d') . '.pdf');
    }
}