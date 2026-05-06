<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExternalIncome extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'date',
        'month',
        'year',
        'source',
        'amount',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'year' => 'integer',
        'amount' => 'decimal:2',
    ];
}
