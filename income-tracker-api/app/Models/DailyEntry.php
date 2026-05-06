<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyEntry extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'date',
        'month',
        'year',
        'platform',
        'account',
        'income',
        'gmv',
        'videos',
        'items_sold',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'year' => 'integer',
        'income' => 'decimal:2',
        'gmv' => 'decimal:2',
        'videos' => 'integer',
        'items_sold' => 'integer',
    ];
}
