<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roam extends Model
{
    protected $fillable = ['sku_id', 'packages', 'support_country', 'image'];

    protected $casts = [
        'packages' => 'array',
        'support_country' => 'array',
    ];


    // In Roam.php
    public function roamSku()
    {
        return $this->hasOne(RoamSku::class, 'sku_id', 'sku_id');
    }
}
