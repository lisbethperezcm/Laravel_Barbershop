<?php
use Illuminate\Http\Request;
use App\Models\BarberDispatch;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberDispatchController;
use App\Http\Controllers\InventoryExitsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Aquí se registran las rutas de la API. Se organizan por categorías para 
| una mejor gestión y mantenimiento.
|
*/

/* 🔹 RUTAS PROTEGIDAS (Requieren autenticación con Sanctum) */
Route::middleware('auth:sanctum')->group(function () {


    /* 📌 CITAS (Appointments) */
Route::post('/appointments', [AppointmentController::class, 'store']); // Crear una nueva cita
Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']); // Actualizar una cita existente
Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']); // Eliminar una cita
Route::get('/clients/appointments', [AppointmentController::class, 'getAppointmentsByClient']); // Listar citas de un cliente específico
  
  
Route::post('/servicios', [ServiceController::class, 'store']);
Route::put('/servicios/{service}', [ServiceController::class, 'update']);
Route::delete('/servicios/{service}', [ServiceController::class, 'destroy']);

    /* 📌 PRODUCTOS (Products) */
Route::post('/products', [ProductController::class, 'store']); // Crear un nuevo producto
Route::delete('/products', [ProductController::class, 'destroy']); // eliminar un producto

/* 📌 Cerrar sesion */
Route::post('/logout', [AuthController::class, 'logout']);


/* 📌 INVENTARIO (Inventory) */
Route::post('/inventory-exits', [InventoryExitsController::class, 'store']);


/* 📌 DESPACHOS (Dispatches) */
Route::post('/barber-dispatch', [BarberDispatchController::class, 'store']);
Route::put('/barber-dispatch/{dispatch}', [BarberDispatchController::class, 'update']);
});


/* 🔹 RUTAS PÚBLICAS */



/* 🔹 AUTENTICACIÓN */
Route::post('/register', [AuthController::class, 'register']); // Registra un nuevo usuario
Route::post('/login', [AuthController::class, 'login']); // Inicia sesión y devuelve un token


// 📌 **BARBEROS (Barbers)**
Route::get('/barbers', [BarberController::class, 'index']); // Listar todos los barberos
Route::delete('/barbers/{barber}', [BarberController::class, 'destroy']); // Eliminar un barbero
Route::post('/barbers/availableSlots', [ScheduleController::class, 'getAvailableSlots']); // Obtener horarios disponibles de los barberos

// 📌 **CLIENTES (Clients)**
Route::get('/clients', [ClientController::class, 'index']); // Listar todos los clientes
Route::delete('/clients', [ClientController::class, 'destroy']); // Eliminar un cliente
// 📌 **ROLES (Roles)**
Route::get('/roles', [RoleController::class, 'index']); // Listar los roles disponibles
Route::get('/roles/{role}', [RoleController::class, 'show']);
Route::put('/roles/{role}', [RoleController::class, 'update']);

// 📌 **CITAS (Appointments)**
Route::get('/appointments', [AppointmentController::class, 'index']); // Listar todas las citas
Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']); // Obtener una cita por ID


// 📌 **FACTURAS (Invoices)**
Route::post('/invoices', [InvoiceController::class, 'store']); // Crear una factura

// 📌 **PRODUCTOS (Products)**
Route::get('/products', [ProductController::class, 'index']); // Listar todos los productos

// 📌 **SERVICIOS (Services)**
Route::get('/servicios/{service}', [ServiceController::class, 'show']);//getServiciosById
Route::get('/servicios', [ServiceController::class, 'index']);//Obtener servicios

/* 📌 DESPACHOS (Dispatches) */
Route::get('/barber-dispatch', [BarberDispatchController::class, 'index']);
Route::get('/barber-dispatch/{dispatch}', [BarberDispatchController::class, 'show']);

