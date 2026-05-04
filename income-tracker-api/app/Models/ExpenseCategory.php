<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['user_id', 'name', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
