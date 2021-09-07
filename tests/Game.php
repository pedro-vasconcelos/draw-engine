<?php

namespace PedroVasconcelos\DrawEngine\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $table = 'games';
    protected $guarded = [];
    
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id', 'id' );
    }
}
