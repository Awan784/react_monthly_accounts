<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsageRight extends Model
{
    protected $fillable = ['user_id', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
