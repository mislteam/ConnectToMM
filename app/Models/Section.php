<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_key',
        'eyebrow_text',
        'title',
        'description',
        'image',
        'video'
    ];

    public function items()
    {
        return $this->hasMany(Item::class, 'section_id');
    }
}
