<?php

namespace App\Helpers;

class GeneralHelper
{
    public static function getFloat($item): float
    {
        return round(floatval($item), 2);
    }
}