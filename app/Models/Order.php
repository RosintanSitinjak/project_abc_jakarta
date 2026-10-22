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

    protected $table = 'orders';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'customer_id',
        'order_number',
        'total_amount',
        'remaining_amount',
        'shipping_fee',            // Biaya ekspedisi/ongkir
        'delivery_method',        // 'pickup' atau 'delivery'
        'shipping_payment_type',  // 'none', 'cod', atau 'prepaid'
        'shipping_address',       // Alamat pengiriman riil
        'notes',                  // Catatan tambahan dari pembeli
        'payment_method',         // 'cash', 'transfer', 'qris', 'kredit'
        'payment_status',         // 'pending', 'waiting_shipping_fee', 'unpaid', 'paid', 'cancelled'
        'payment_proof',
        'payment_proof_id',
        'due_date',
        'shipping_status',        // 'pending', 'processing', 'shipping', 'delivered', 'cancelled'
        'tracking_number',
        'courier_name',
        'source',
    ];

    protected $casts = [
        'total_amount'     => 'integer',
        'remaining_amount' => 'integer',
        'shipping_fee'     => 'integer',
        'due_date'         => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Relasi ke Pelanggan (Jemaat, PL, Gereja, Sekolah)
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relasi ke Item Buku yang dibeli
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Relasi ke Bukti Pembayaran (Attachments Polymorphic/Single)
     */
    public function thumbnail(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'payment_proof_id');
    }

    /**
     * Relasi ke Riwayat Pembayaran Hutang / Cicilan
     */
    public function payments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'customer_id', 'customer_id');
    }
}