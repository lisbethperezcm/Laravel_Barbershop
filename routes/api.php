<?php

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AppointmentController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
//Registro de usuarios

// Ruta para obtener los roles
Route::get('roles', [RoleController::class, 'index']);

Route::post('/registro', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);
//Obtener barberos
Route::get('/barberos', [BarberController::class, 'index']);

//Obtener clientes
Route::get('/clientes', [ClientController::class, 'index']);

//Crear clita 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/appointments', [AppointmentController::class, 'store']);
    
});

Route::get('/appointments', [AppointmentController::class, 'index']);


Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return response()->json(['message' => 'Enlace de verificación enviado.'], 200);
})->middleware(['auth:sanctum', 'throttle:6,1'])->name('verification.send');

// Ruta para verificar el correo

/*
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(['message' => 'Correo verificado correctamente.'], 200);
})->middleware(['signed'])->name('verification.verify');
*/

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return response()->json(['message' => 'Correo verificado correctamente.'], 200);
})->middleware(['auth:sanctum','signed'])->name('verification.verify');
// Ruta para acceder a recursos protegidos
Route::get('/protected-route', function () {
    return response()->json(['message' => 'Bienvenido a una ruta protegida.'], 200);
})->middleware(['auth:sanctum', 'verified']);