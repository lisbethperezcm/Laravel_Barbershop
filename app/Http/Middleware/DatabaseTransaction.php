<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseTransaction
{
    public function handle($request, Closure $next)
    {
        DB::beginTransaction();

        try {
            $response = $next($request);
            DB::commit();
            return $response;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e; // Deja que el handler de excepciones responda
        }
    }
}
