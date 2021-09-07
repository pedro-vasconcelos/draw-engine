<?php
namespace PedroVasconcelos\DrawEngine\Traits;

use PedroVasconcelos\DrawEngine\Models\Winner;

trait HasWinner
{
    public function winner()
    {
        return $this->morphOne(Winner::class, 'game');
    }
}
