<?php

namespace PedroVasconcelos\DrawEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Winner extends Model
{
    use HasFactory;

    protected $table = 'winners';
    protected $guarded = [];

    public function game()
    {
        return $this->morphTo();
    }
    
    public function draw()
    {
        return $this->morphTo();
    }
    
    public function prize()
    {
        return $this->morphOne(app(config('draw-engine.models.prize')), 'winner');
    }
}
