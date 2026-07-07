<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasUuids;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['attachmentable_id', 'attachmentable_type', 'name', 'path', 'type'];

    // --- BUMBU RAHASIA (ACCESSOR) ---
    // Setiap kali Nuxt panggil data, otomatis ada kolom 'url' yang berisi link gambar
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->path);
    }
}