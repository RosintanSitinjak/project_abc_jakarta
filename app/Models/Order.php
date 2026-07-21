<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'remaining_amount',
        'payment_method',
        'payment_status',
        'payment_proof',    // Simpan URL bukti (opsional)
        'payment_proof_id', // PENTING: Tambahkan ini untuk relasi gambar
        'due_date',
        'shipping_status',
        'tracking_number',
        'courier_name',
        'source',
    ];

    // Relasi ke Pelanggan
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke detail barang (Buku apa saja yang dibeli)
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * RELASI KE GAMBAR BUKTI PEMBAYARAN
     * Menghubungkan ke tabel attachments
     */
    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'payment_proof_id');
    }
}