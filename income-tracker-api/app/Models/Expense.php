<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'user_id',
        'client_id',
        'date',
        'month',
        'year',
        'category',
        'platform',
        'amount',
        'receipt_disk',
        'receipt_path',
        'receipt_original_name',
        'receipt_mime',
        'receipt_size',
        'notes',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'year' => 'integer',
        'amount' => 'decimal:2',
        'receipt_size' => 'integer',
    ];
}

