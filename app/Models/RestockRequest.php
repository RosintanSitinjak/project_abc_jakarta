<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestockRequest extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'book_id', 
        'user_id',          // Menyesuaikan database: ID admin yang mengajukan
        'quantity',         // Menyesuaikan database: Jumlah stok yang diminta
        'status',           // pending, approved, rejected
        'pimpinan_notes',   // Menyesuaikan database: Komentar/alasan dari Pak Andi
        'is_completed', 
        'completed_at'
    ];

    public function book() 
    {
        return $this->belongsTo(Book::class);
    }

    // Relasi ke admin pemohon (menggunakan foreign key user_id)
    public function creator() 
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}