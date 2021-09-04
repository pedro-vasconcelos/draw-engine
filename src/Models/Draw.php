<?php

namespace PedroVasconcelos\DrawEngine\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draw extends Model
{
    use HasFactory;

    protected $table = 'draws';
    protected $guarded = [];

}
