<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CapMonthlyActiveAppointments
{
    private const TIMEZONE = 'America/Santo_Domingo';
    private const MAX_ACTIVE_PER_MONTH = 5;          // tope mensual
    private const ACTIVE_STATUS_IDS = [3, 5];        // 3=Reservado, 5=En proceso
    private const DATE_FIELD = 'appointment_date';   // YYYY-MM-DD (DATE)

    public function handle(Request $request, Closure $next): Response
    {
        $user  = Auth::user();
        $role  = $user->role ?? null;  // Role object or null
        // 1) Si el request trae client_id => validar SIEMPRE (admin/barbero creando para un cliente)
        if ($request->filled('client_id')) {
            $clientId = (int) $request->input('client_id');
        } else {
            // 2) Si NO trae client_id:
           
            if (strtoupper((string) $role) !== 'CLIENT') {
                return $next($request); // omite validación para barbero/admin sin client_id 
            }
            $clientId = $this->resolveClientIdForClientUser($user);
            if (!$clientId) {
               
                return $next($request);
            }
        }

      
        $now  = Carbon::now(self::TIMEZONE);
        $from = $now->copy()->startOfMonth()->toDateString();
        $to   = $now->copy()->endOfMonth()->toDateString();

        // Contar citas activas del cliente en el mes actual
        $query = DB::table('appointments')
            ->where('client_id', $clientId)
            ->whereBetween(self::DATE_FIELD, [$from, $to])
            ->whereIn('status_id', self::ACTIVE_STATUS_IDS);

       
        $query->where(self::DATE_FIELD, '>=', $now->toDateString());

        $activeThisMonth = $query->count();

        if ($activeThisMonth >= self::MAX_ACTIVE_PER_MONTH) {
            // 429: límite excedido (con header para debug)
            return response()->json([
                'message' => 'Límite mensual alcanzado: ' . self::MAX_ACTIVE_PER_MONTH . ' citas activas. Finaliza o cancela alguna para reservar otra.'
            ], 429)->header('X-Debug-Limit', 'CapMonthlyActiveAppointments');
        }

        return $next($request);
    }

    /**
     * Resolver client_id cuando el usuario autenticado es CLIENT.
     * 
     */
    private function resolveClientIdForClientUser($user): ?int
    {
        try {
            if (isset($user->person?->client?->id)) {
                return (int) $user->person->client->id;
            }
        } catch (\Throwable $e) {
            // ignora y retorna null
        }

        return null;
    }
}
