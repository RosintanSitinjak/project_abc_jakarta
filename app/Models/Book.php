<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    // PASTIKAN SEMUA KOLOM INI ADA DI SINI
    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'author',
        'isbn',
        'price',
        'member_price', // Tambahkan jika belum ada
        'stock',        // Tambahkan jika belum ada
        'rop_point',    // Tambahkan jika belum ada
        'description',  // Tambahkan jika belum ada
        'thumbnail_id'  // Tambahkan jika belum ada
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'thumbnail_id');
    }
}