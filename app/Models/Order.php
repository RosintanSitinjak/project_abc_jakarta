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
        'payment_method',
        'payment_status',
        'payment_proof',
        'due_date',
        'shipping_status',
        'tracking_number',
        'courier_name',
        'source', // Penting untuk membedakan pesanan Web vs Manual
    ];

    // Relasi ke Pelanggan
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    // Relasi ke detail barang yang dibeli
    // public function items(): HasMany
    // {
    //     return $this->hasMany(OrderItem::class);
    // }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(OrderItem::class);
}
}