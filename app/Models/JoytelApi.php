<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoytelApi extends Model
{
    use HasFactory;

    protected $table = 'joytel_api';

    protected $fillable = [
        'customer_code',
        'customer_auth',
        'api_url',
        'rsp_appid',
        'rsp_secret',
        'rsp_baseurl'
    ];
}
