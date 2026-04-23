<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoamSku extends Model
{
    use HasFactory;
    protected $fillable = [
        'sku_id',
        'country_name',
        'country_code',
        'status',
    ];

    public function roam()
    {
        return $this->hasOne(Roam::class, 'sku_id', 'sku_id');
    }
}
