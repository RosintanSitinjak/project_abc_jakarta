<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ArticleImageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $request->validate([
            'upload' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'], // 5MB Max
            'UploadFiles' => ['sometimes', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ]);

        if ($request->hasFile('upload') || $request->hasFile('UploadFiles')) {
            $file = $request->file('upload') ?? $request->file('UploadFiles');
            $path = $file->store('articles/images', 'public');

            return response()->json([
                'url' => asset('storage/' . $path)
            ]);
        }

        throw ValidationException::withMessages(['upload' => 'No image uploaded']);
    }
}
