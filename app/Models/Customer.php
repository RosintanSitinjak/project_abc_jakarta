<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- WAJIB TAMBAHKAN INI
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
        'type',
        'status', // 'pending', 'approved', 'rejected'
        'address',
        'phone',
        'credit_limit',
        'current_debt',
    ];

    /**
     * RELASI KE LOGIN USER
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * RELASI KE PESANAN (BARU DITAMBAHKAN)
     * Gunanya agar kita bisa menghitung total hutang dari riwayat belanja
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}