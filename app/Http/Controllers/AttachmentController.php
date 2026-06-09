<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\UpdateAttachmentRequest;
use App\Models\Attachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class AttachmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request): JsonResponse
    {
        $query = Attachment::query()->latest();
        $attachments = $request->paginated ? $query->paginate($request->itemsPerPage) : $query->get();

        return response()->json($attachments);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttachmentRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $mime = $file->getMimeType();
            $isImage = in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

            if ($isImage) {
                $path = $this->compressAndStore($file);
                $storedSize = Storage::disk('public')->size($path);
            } else {
                $path = $file->store('attachments', 'public');
                $storedSize = $file->getSize();
            }

            $data['name'] = $data['name'] ?? $file->getClientOriginalName();
            $data['path'] = Storage::disk('public')->url($path);
            $data['size'] = $storedSize;
            $data['mime'] = $mime;
            $data['disk'] = $data['disk'] ?? 'public';
            $data['folder'] = $data['folder'] ?? 'attachments';
        }

        unset($data['file']);

        $attachment = Attachment::create($data);

        return response()->json($attachment, 201);
    }

    /**
     * Compress and store an uploaded image file.
     *
     * Resizes to max 1920px (preserving aspect ratio) and encodes at 80% quality.
     */
    private function compressAndStore($file): string
    {
        $manager = ImageManager::gd();
        $image = $manager->read($file->getPathname());

        // Scale down if larger than 1920px on either axis
        $image->scaleDown(width: 1920, height: 1920);

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $filename = 'attachments/' . \Illuminate\Support\Str::uuid() . '.' . $extension;

        $mime = $file->getMimeType();
        $quality = 80;

        $encoded = match ($mime) {
            'image/png' => $image->toPng(),
            'image/webp' => $image->toWebp($quality),
            'image/gif' => $image->toGif(),
            default => $image->toJpeg($quality),
        };

        Storage::disk('public')->put($filename, (string) $encoded);

        return $filename;
    }

    /**
     * Display the specified resource.
     */
    public function show(Attachment $attachment): JsonResponse
    {
        return response()->json($attachment);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttachmentRequest $request, Attachment $attachment): JsonResponse
    {
        $attachment->update($request->validated());

        return response()->json($attachment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attachment $attachment): JsonResponse
    {
        $attachment->delete();

        return response()->json(['status' => 'deleted']);
    }
}
