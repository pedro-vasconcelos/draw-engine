<?php

namespace PedroVasconcelos\DrawEngine\Models;

use Illuminate\Database\Eloquent\Model;
use PedroVasconcelos\DrawEngine\Traits\HasPrizeSchedule;
use PedroVasconcelos\DrawEngine\Traits\HasWinnerGame;

class Draw extends Model
{
    use HasPrizeSchedule;
    use HasWinnerGame;

    protected $table = 'draws';
    protected $guarded = [];

    protected $casts = [
        'start_period' => 'datetime',
        'end_period' => 'datetime',
    ];

}
