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
        'qty_requested', 
        'qty_approved', 
        'status', 
        'admin_note', 
        'leader_comment', 
        'created_by', 
        'approved_by', 
        'is_completed', 
        'completed_at'
    ];

    public function book() 
    {
        return $this->belongsTo(Book::class);
    }

    // Relasi ke pemohon (Admin)
    public function creator() 
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi ke pimpinan (Pak Andi)
    public function approver() 
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}