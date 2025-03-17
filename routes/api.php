<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API. Se organizan por categorías para 
| una mejor gestión y mantenimiento.
|
*/

/* 🔹 AUTENTICACIÓN */
Route::post('/register', [AuthController::class, 'register']); // Registra un nuevo usuario
Route::post('/login', [AuthController::class, 'login']); // Inicia sesión y devuelve un token

/* 🔹 RUTAS PROTEGIDAS (Requieren autenticación con Sanctum) */
Route::middleware('auth:sanctum')->group(function () {

    /* 📌 CITAS (Appointments) */
    Route::post('/appointments', [AppointmentController::class, 'store']); // Crear una nueva cita
    Route::put('/appointment/{appointment}', [AppointmentController::class, 'update']); // Actualizar una cita existente
    Route::get('/clients/appointments', [AppointmentController::class, 'getAppointmentsByClient']); // Listar citas de un cliente específico

    /* 📌 PRODUCTOS (Products) */
    Route::post('/products', [ProductController::class, 'store']); // Crear un nuevo producto

});

/* 🔹 RUTAS PÚBLICAS */

// 📌 **BARBEROS (Barbers)**
Route::get('/barbers', [BarberController::class, 'index']); // Listar todos los barberos
Route::post('/barbers/availableSlots', [ScheduleController::class, 'getAvailableSlots']); // Obtener horarios disponibles de los barberos

// 📌 **CLIENTES (Clients)**
Route::get('/clients', [ClientController::class, 'index']); // Listar todos los clientes

// 📌 **ROLES (Roles)**
Route::get('/roles', [RoleController::class, 'index']); // Listar los roles disponibles

// 📌 **CITAS (Appointments)**
Route::get('/appointments', [AppointmentController::class, 'index']); // Listar todas las citas
Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']); // Obtener una cita por ID

// 📌 **FACTURAS (Invoices)**
Route::post('/invoices', [InvoiceController::class, 'store']); // Crear una factura

// 📌 **PRODUCTOS (Products)**
Route::get('/products', [ProductController::class, 'index']); // Listar todos los productos





