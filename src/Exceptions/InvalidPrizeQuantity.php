<?php

namespace PedroVasconcelos\DrawEngine\Exceptions;

use Exception;

class InvalidPrizeQuantity extends Exception
{
    public static function couldNotMakeCalculations($days, $prizes, $cap)
    {
        return new static("Prizes($prizes) / Days($days) must be less or equal to Cap($cap)");
    }

    public static function maxDailyPrize()
    {
        return new static("Maximum daily prize limit reached.");
    }
}
