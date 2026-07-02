<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
   use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'category_id',
        'title',
        'author',       // Tambahkan ini
        'isbn',         // Tambahkan ini
        'price',        // Tambahkan ini
        'member_price', // Tambahkan ini (Harga khusus Penginjil)
        'stock',        // Tambahkan ini
        'rop_point',    // Tambahkan ini (Algoritma ROP)
        'description',
        'cover_image',  // Tambahkan ini
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function thumbnail(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachmentable')
            ->where('type', 'thumbnail');
    }
}
