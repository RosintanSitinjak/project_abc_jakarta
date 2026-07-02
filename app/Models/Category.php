<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug', // GANTI 'description' jadi 'slug' sesuai migration kita tadi
    ];

    // GANTI nama fungsi dari products() menjadi books()
    public function books(): HasMany
    {
        return $this->hasMany(Book::class); // Arahkan ke model Book
    }

    // Portfolios hapus saja kalau tidak dipakai di ABC Jakarta
}