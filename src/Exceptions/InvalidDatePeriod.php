<?php

namespace PedroVasconcelos\DrawEngine\Exceptions;

use Exception;

class InvalidDatePeriod extends Exception
{
    public static function couldNotMakePeriod()
    {
        return new static("Check dates");
    }

}
