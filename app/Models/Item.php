<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_section_id',
        'item_image',
        'person_image',
        'title',
        'description',
        'button_text',
        'button_url',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }
}
