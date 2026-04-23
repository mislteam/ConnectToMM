<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoamPhysicalSku extends Model
{
    use HasFactory;
     protected $fillable = [
        'dp_id',
        'sku_id',
        'country_name',
        'country_code',
        'status',
    ];

    public function roamPhysical()
    {
        return $this->hasMany(RoamPhysical::class, 'sku_id', 'sku_id');
    }
}
