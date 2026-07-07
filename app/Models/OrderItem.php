<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    // Kolom yang boleh diisi (Mass Assignable)
    protected $fillable = [
        'order_id',
        'book_id',
        'qty',
        'price_at_purchase', // Penting: harga saat beli (takutnya harga buku naik di masa depan)
    ];

    // Relasi balik ke buku
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    // Relasi balik ke pesanan utama
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}