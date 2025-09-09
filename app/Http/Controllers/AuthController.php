<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Barber;
use App\Models\Person;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\LoginRequest;
use App\Mail\TemporaryPasswordMail;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendWelcomeNotification;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\CareTipCollection;
use App\Notifications\WelcomeNotification;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Requests\ChangePasswordRequest;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {



        // La validación ya se maneja automáticamente con RegisterRequest
        $request->validated();

        $role = Role::findOrFail($request->role_id);

        // 🔹 Generar contraseña temporal si no viene en la request
        $plainPassword = $request->password ?? Str::random(10);

        $user = User::create([
            'email' => $request->email,
            'password' => bcrypt($plainPassword),
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

          // 🔹 Si la contraseña fue generada automáticamente, enviar correo con la contraseña temporal
    if (!$request->filled('password')) {
        Mail::to($user->email)->send(new TemporaryPasswordMail($plainPassword));
    }

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
    }
    public function login(LoginRequest $request)
    {

        $data = $request->only('email', 'password');

        if (!Auth::attempt($data)) {
            return response([
                'message' => 'Credenciales incorrectas.',
                'errorCode' => '401'
            ], 401);
        }

        $user = Auth::user();

        if (!$user) {
            return response([
                'message' => 'No se pudo autenticar al usuario.',
                'errorCode' => '401'
            ], 401);
        }

        /** @var \Illuminate\Foundation\Auth\User $user */
        $token = $user->createToken('access_token')->plainTextToken;


        // Obtener datos del usuario y su relación con persona y role
        $person = $user->person;
        $role = $user->role->name;

        // Obtener care tips recomendados según los últimos 3 servicios del cliente
        $careTips = [];
        if ($role === 'Cliente') {
            $client = $user->person->client;
            if ($client) {
                $lastServices = $client->lastThreeServices();

                if (!empty($lastServices)) {

                    $careTips = \App\Models\CareTip::getTipsByServices($lastServices);
                }
            }
        }

        return [
            'access_token' => $token, // Esto retorna el token correctamente
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
            'care_tips' => new CareTipCollection($careTips),
            'errorCode' => '200'
        ];
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Verificar contraseña actual
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.'
            ], 422);
        }

        // Evitar reutilizar la misma contraseña
        if (Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'La nueva contraseña no puede ser igual a la actual.'
            ], 422);
        }

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($request->password),
        ]);

       

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.',
            'errorCode' => '200'
        ], 200);
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
