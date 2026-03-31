<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoamPhysical extends Model
{
    use HasFactory;

    protected $fillable = ['dp_id','sku_id', 'packages', 'support_country', 'image'];

    protected $casts = [
        'packages' => 'array',
        'support_country' => 'array',
    ];


    // In Roam.php
    public function roamPhysicalSku() {
        return $this->hasOne(RoamPhysicalSku::class, 'sku_id', 'sku_id');
    }
}
