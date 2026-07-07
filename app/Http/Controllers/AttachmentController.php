<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class AttachmentController extends Controller
{
    /**
     * Fungsi untuk memproses upload gambar dari Nuxt
     */
public function store(Request $request): JsonResponse
{
    $request->validate([
        'file' => 'required|image|max:2048',
        'attachmentable_type' => 'required|string',
        'type' => 'required|string',
    ]);

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        
        // Simpan file secara fisik di storage/app/public/attachments
        $path = $file->store('attachments', 'public');

        // Simpan ke database
        $attachment = Attachment::create([
            // Gunakan string random jika UUID manual bermasalah, 
            // atau biarkan kosong jika sudah pakai Trait HasUuids di Model
            'attachmentable_id' => (string) \Illuminate\Support\Str::uuid(), 
            'attachmentable_type' => $request->attachmentable_type,
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'type' => $request->type,
        ]);

        return response()->json($attachment, 201);
    }

    return response()->json(['message' => 'File gagal diunggah'], 400);
}}