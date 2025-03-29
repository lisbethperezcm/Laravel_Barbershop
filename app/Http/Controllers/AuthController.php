<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Barber;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendWelcomeNotification;
use App\Http\Requests\RegisterRequest;
use App\Notifications\WelcomeNotification;
use Illuminate\Console\Scheduling\Schedule;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {

        return DB::transaction(function () use ($request) {

            // La validación ya se maneja automáticamente con RegisterRequest
            $request->validated();

            $role = Role::findOrFail($request->role_id);

            $user = User::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => $request->role_id,
            ]);
            // Crear la persona asociada al usuario
            $person = app(\App\Http\Controllers\PersonController::class)->store($user, $request);

            // Validacion del rol Barbero
            if ($role->id == 2) {
                app(\App\Http\Controllers\BarberController::class)->store($person, $request);
            }
            // Validacion del rol Cliente
            if ($role->id == 3) {
                app(\App\Http\Controllers\ClientController::class)->store($person);
            }

            $token = $user->createToken('access_token')->plainTextToken;
            /* 
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60), ['id' => $user->id, 'hash' => sha1($user->email)]
        );*/

            // $user->sendEmailVerificationNotification();

            $user->notify(new WelcomeNotification());


            // 🔹 Enviar la notificación en segundo plano
            //  dispatch(new SendWelcomeNotification($user));

            // Retornar una respuesta de éxito
            return response()->json([
                'message' => 'Usuario registrado exitosamente',
                'access_token' => $token,
                'token_type' => 'Bearer',
                // 'verification_url' => $verificationUrl,
                'user' => new UserResource($user),
                'errorCode' => '201'
            ], 201);
        });
    }
    public function login(LoginRequest $request)
    {

        $data = $request->only('email', 'password');

        if (!Auth::attempt($data)) {
            return response([
                'errors' => ['Credenciales incorrectas.'],
                'errorCode' => '401'
            ], 401);
        }

        $user = Auth::user();

        if (!$user) {
            return response([
                'errors' => ['No se pudo autenticar al usuario.'],
                'errorCode' => '422'
            ], 422);
        }

        /** @var \Illuminate\Foundation\Auth\User $user */
        $token = $user->createToken('access_token')->plainTextToken;


        // Obtener datos del usuario y su relación con persona y role
        $person = $user->person;
        $role = $user->role->name;


        return [
            'access_token' => $token, // Esto retorna el token correctamente
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'errorCode' => '200'
        ];
    }

    public function logout(Request $request)
    {
        // Revocar el token actual del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
            'errorCode' => '200'
        ], 200);
    }
}
