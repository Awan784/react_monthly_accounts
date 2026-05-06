<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandDeal extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'date',
        'month',
        'year',
        'platform',
        'account',
        'brand',
        'contact',
        'product',
        'amount',
        'status',
        'due_date',
        'usage_rights',
        'contract',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'year' => 'integer',
        'amount' => 'decimal:2',
        'due_date' => 'date:Y-m-d',
        'contract' => 'array',
    ];
}
