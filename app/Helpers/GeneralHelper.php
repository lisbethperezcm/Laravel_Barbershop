<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GeneralHelper
{
    public static function getFloat($item): string
    {
        return number_format(round(floatval($item), 2), 2, '.', ',');
    }


    /**
     * Aplica filtros de fecha en una consulta Eloquent según los parámetros de Request.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  string  $column
     * @return Builder
     */
    public static function applyDateFilter($query, $request, $column)
    {
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween($column, [$request->fecha_inicio, $request->fecha_fin]);
        } elseif ($request->filled('fecha_inicio')) {
            $query->whereDate($column, '>=', $request->fecha_inicio);
        } elseif ($request->filled('fecha_fin')) {
            $query->whereDate($column, '<=', $request->fecha_fin);
        }
    }
}
