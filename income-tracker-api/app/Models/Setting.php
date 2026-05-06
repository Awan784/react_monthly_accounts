<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['user_id', 'tax_rate', 'income_goal', 'monthly_goal', 'pricing_version', 'pricing'];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'income_goal' => 'decimal:2',
        'monthly_goal' => 'decimal:2',
        'pricing' => 'array',
    ];
}
