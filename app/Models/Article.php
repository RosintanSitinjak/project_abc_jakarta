<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'content',
        'excerpt',  // Baru
        'status',   // Baru
        'thumbnail_id'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function thumbnail()
    {
        return $this->belongsTo(Attachment::class, 'thumbnail_id');
    }
}