<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    /** @use HasFactory<\Database\Factories\ArticleFactory> */
    use HasFactory;
    use HasUuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'description',
        'author_id',
        'views',
    ];

    public function thumbnail()
    {
        return $this->morphOne(Attachment::class, 'attachmentable')
            ->where('type', 'thumbnail');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
