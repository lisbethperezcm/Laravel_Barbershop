<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class DateHelper
{

public static function formatTime12($time)
    {
        return Carbon::parse($time)->format('g:i A');
    }

    public static function formatDateLong($date)
    {
        return Carbon::parse($date)->translatedFormat('d \d\e F \d\e Y');
        // Ejemplo: "08 de agosto de 2025"
    }

    public static function formatDateShort($date)
    {
        return Carbon::parse($date)->format('d/m/Y');
    }





}



