<?php

namespace App\Helpers;

class GeneralHelper
{
    public static function getFloat($item): string
    {
        return number_format(round(floatval($item), 2), 2, '.', ',');
    }
}