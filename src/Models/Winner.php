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
        return $this->belongsTo(Game::class);
    }
}
