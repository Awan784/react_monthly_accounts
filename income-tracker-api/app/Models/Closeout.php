<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Closeout extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'tiktok',
        'brands',
        'expenses',
        'backup',
    ];

    protected $casts = [
        'year' => 'integer',
        'tiktok' => 'boolean',
        'brands' => 'boolean',
        'expenses' => 'boolean',
        'backup' => 'boolean',
    ];
}

