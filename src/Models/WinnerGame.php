<?php

namespace PedroVasconcelos\DrawEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WinnerGame extends Model
{
    use HasFactory;

    protected $table = 'winner_games';
    protected $guarded = [];
    protected $casts = [
        'date' => 'date',
    ];
    
    public function draw()
    {
        return $this->morphTo();
    }
    
}
