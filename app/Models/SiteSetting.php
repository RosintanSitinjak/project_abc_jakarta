<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class SiteSetting extends Model
{
    /** @use HasFactory<\Database\Factories\SiteSettingFactory> */
    use HasFactory;
    use HasUuids, SoftDeletes;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'about_us',
        'vision',
        'mission',
        'whatsapp_number',
        'address',
        'phone',
        'email',
        'site_name',
        'linkedin_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'site_description',
    ];

    public function logo(): MorphOne
    {
        return $this->morphOne(Attachment::class, 'attachmentable')
            ->where('type', 'logo');
    }
}
