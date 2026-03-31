<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactInfo extends Model
{
    use HasFactory;
    protected $casts = [
        'social_media_links' => 'array'
    ];

    protected $fillable = [
        'description',
        'email',
        'phone',
        'social_media_links',
        'joytel_image',
        'roam_image'
    ];

    public function getSocialMediaTitlesAttribute()
    {
        return collect($this->social_media_links)->pluck('title');
    }
}
