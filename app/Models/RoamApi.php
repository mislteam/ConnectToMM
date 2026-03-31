<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class RoamApi extends Model
{
    use HasFactory;
    protected $table = 'roam_api';

    protected $fillable = [
        'client_id',
        'secret_key',
        'client_key',
        'api_url',
    ];
}
