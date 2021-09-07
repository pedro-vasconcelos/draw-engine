<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draw extends Model
{
    use HasFactory;

    protected $table = 'draws';
    protected $guarded = [];
    protected $casts = [
        'daily_prize_cap' => 'integer',
        'prizes' => 'integer',
        'algorithm' => 'string',
        'type' => 'string',
        'week' => 'integer',
        'month' => 'integer',
        'start_period' => 'datetime',
        'end_period' => 'datetime',
    ];
    
}
