<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $table = 'regions';
    protected $guarded = [];

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function draw()
    {
        return $this->belongsTo(Draw::class, 'draw_id', 'id' );
    }


    public function games()
    {
        return $this->hasMany(Game::class, 'region_id', 'id');
    }

}
