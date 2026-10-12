<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'path',
        'name',
        'mime_type',
        'size',
        'attachmentable_id',
        'attachmentable_type',
        'type',
    ];

    /**
     * RELASI POLIMORFIS (INI YANG HILANG)
     * Agar satu tabel attachment bisa dipakai oleh Book, Article, dan Order.
     */
    public function attachmentable(): MorphTo
    {
        return $this->morphTo();
    }
}