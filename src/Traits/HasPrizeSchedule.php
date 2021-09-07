<?php
namespace PedroVasconcelos\DrawEngine\Traits;

use PedroVasconcelos\DrawEngine\Models\PrizeDeliverySchedule;

trait HasPrizeSchedule
{
    public function schedule()
    {
        return $this->morphMany(PrizeDeliverySchedule::class, 'draw');
    }
}
