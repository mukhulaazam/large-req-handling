<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $guarded = [];

    protected $casts = [
        'request'  => 'array',
        'metadata' => 'array',
    ];
}
