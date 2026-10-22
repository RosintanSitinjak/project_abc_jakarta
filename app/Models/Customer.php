<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'customers';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'type',             // 'jemaat', 'gereja', 'sekolah', 'penginjil'
        'status',           // 'pending', 'approved', 'rejected'
        'rejection_reason',
        'address',
        'phone',
        'credit_limit',     // Plafon kredit: 0 untuk jemaat, 5.000.000 untuk penginjil
        'current_debt',     // Akumulasi sisa hutang berjalan
    ];

    protected $casts = [
        'credit_limit' => 'integer',
        'current_debt' => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /**
     * RELASI KE LOGIN USER
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELASI KE RIWAYAT PESANAN
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /**
     * RELASI KE RIWAYAT PEMBAYARAN CICILAN
     */
    public function debtPayments(): HasMany
    {
        return $this->hasMany(DebtPayment::class, 'customer_id');
    }
}