<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo; 
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'customers'; // Tegaskan nama tabelnya

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',      // Tambahkan ini
        'name',
        'type',         // Tambahkan ini (Gereja/Penginjil/Umum)
        'address',      // Tambahkan ini
        'phone',        // Tambahkan ini
        'credit_limit', // Tambahkan ini
        'current_debt', // Tambahkan ini
    ];


    public function user(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }
    }