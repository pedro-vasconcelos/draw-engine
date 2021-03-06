<?php

namespace PedroVasconcelos\DrawEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrizeDeliverySchedule extends Model
{
    use HasFactory;

    protected $table = 'prize_delivery_schedule';
    protected $guarded = [];
    protected $casts = [
        'date' => 'date',
    ];
    
    public function draw()
    {
        return $this->morphTo();
    }
    
}
