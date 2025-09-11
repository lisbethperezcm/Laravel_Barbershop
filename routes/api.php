<?php

use App\Models\Service;
use Illuminate\Http\Request;
use App\Models\BarberDispatch;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\CareTipController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\BarberDispatchController;
use App\Http\Controllers\InventoryExitsController;
use App\Http\Controllers\InventoryEntryController;

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


    // --- Rutas SIN transacción (lecturas, listados, etc.)

    /* 📌 Cerrar sesion */
    Route::post('/logout', [AuthController::class, 'logout']);

    /* 📌 INVENTARIO (Inventory) */
    Route::get('/inventory-entries', [InventoryEntryController::class, 'index']);
    /*📌 NOTIFICACIONES (Notifications) */
    Route::get('/v1/notifications', function (Request $r) {
        return $r->user()->notifications()->latest()->paginate(20);
    });

    /* 📌 HORARIOS (Schedules) */
    Route::post('/barbers/availableSlots', [ScheduleController::class, 'getAvailableSlots']); // Obtener horarios disponibles de los barberos
    Route::get('/barbers/schedules', [ScheduleController::class, 'index']);
 

    // --- Rutas CON transacción (crear/editar/eliminar)

    Route::middleware(['db.transaction'])->group(function () {

        /* 📌 CITAS (Appointments) */
        Route::post('/appointments', [AppointmentController::class, 'store']); // Crear una nueva cita
        Route::put('/appointments/{appointment}', [AppointmentController::class, 'update']); // Actualizar una cita existente
        Route::put('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus']); //Actualizar estatus de la cita
        Route::delete('/appointments/{appointment}', [AppointmentController::class, 'destroy']); // Eliminar una cita

        /* 📌 SERVICIOS (Services) */
        Route::post('/servicios', [ServiceController::class, 'store']);
        Route::put('/servicios/{service}', [ServiceController::class, 'update']);
        Route::delete('/servicios/{service}', [ServiceController::class, 'destroy']);

        /* 📌 PRODUCTOS (Products) */
        Route::post('/products', [ProductController::class, 'store']); // Crear un nuevo producto
        Route::put('/products/{product}', [ProductController::class, 'update']); // Actualizar un producto existente
        Route::delete('/products/{product}', [ProductController::class, 'destroy']); // eliminar un producto

        /* 📌 INVENTARIO (Inventory) */
        Route::post('/inventory-exits', [InventoryExitsController::class, 'store']);
        Route::get('/inventory-exits', [InventoryExitsController::class, 'index']);
        Route::delete('/inventory-exits/{inventoryExit}', [InventoryExitsController::class, 'destroy']);
        Route::put('/inventory-exits/{inventoryExit}', [InventoryExitsController::class, 'update']);

        Route::post('/inventory-entries', [InventoryEntryController::class, 'store']);
        Route::put('/inventory-entries/{inventoryEntry}', [InventoryEntryController::class, 'update']);

        // 📌 **FACTURAS (Invoices)*/
        Route::post('/invoices', [InvoiceController::class, 'store']); // Crear una factura
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']); // Eliminar una factura

        /* 📌 DESPACHOS (Dispatches) */
        Route::post('/barber-dispatch', [BarberDispatchController::class, 'store']);
        Route::put('/barber-dispatch/{dispatch}', [BarberDispatchController::class, 'update']);
      
        /* 📌 TIPS DE CUIDADO (Care Tips) */
        Route::post('/care-tips', [CareTipController::class, 'store']); // Crear un tip
        Route::put('/care-tips/{careTip}', [CareTipController::class, 'update']); // Actualizar un tip
        Route::delete('/care-tips/{careTip}', [CareTipController::class, 'destroy']); // Eliminar un tip

        /* 📌 USUARIOS (Users) */
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        /* 📌 BARBEROS (Barbers) */
        Route::put('/barbers/{barber}', [BarberController::class, 'update']); // Actualizar un barbero

         /* 📌 HORARIOS (Schedules) */
           Route::put('/barbers/schedules/{barber}', [ScheduleController::class, 'update']); //Actualizar horario de barbero

        /* 📌 CLIENTES (Clients) */
        Route::put('/clients', [ClientController::class, 'update']); // Actualizar un cliente
    });
});



/* 🔹 RUTAS PÚBLICAS */


/* 🔹 AUTENTICACIÓN */
Route::post('/register', [AuthController::class, 'register']); // Registra un nuevo usuario
Route::post('/login', [AuthController::class, 'login']); // Inicia sesión y devuelve un token


// 📌 **BARBEROS (Barbers)**
Route::get('/barbers', [BarberController::class, 'index']); // Listar todos los barberos
Route::get('/barbers/{barber}', [BarberController::class, 'show']); // Obtener un barbero por ID
Route::delete('/barbers/{barber}', [BarberController::class, 'destroy']); // Eliminar un barbero
Route::post('schedules/toggle-status', [ScheduleController::class, 'toggleStatus']); // Inactivar y activar un horario

// 📌 **CLIENTES (Clients)**
Route::get('/clients', [ClientController::class, 'index']); // Listar todos los clientes
Route::get('/clients/{client}', [ClientController::class, 'show']); // Obtener un cliente por ID
Route::delete('/clients', [ClientController::class, 'destroy']); // Eliminar un cliente
// 📌 **ROLES (Roles)**
Route::get('/roles', [RoleController::class, 'index']); // Listar los roles disponibles
Route::get('/roles/{role}', [RoleController::class, 'show']);
Route::put('/roles/{role}', [RoleController::class, 'update']);

// 📌 **CITAS (Appointments)**
Route::get('/appointments', [AppointmentController::class, 'index']); // Listar todas las citas
Route::get('/appointments/weekly', [AppointmentController::class, 'getWeeklyAppointment']); // Obtener citas de la semana
Route::get('/appointments/{appointment}', [AppointmentController::class, 'show']); // Obtener una cita por ID
Route::get('/clients-appointments', [AppointmentController::class, 'getAppointmentByClient']); // Listar citas de un cliente específico
Route::get('/barbers-appointments', [AppointmentController::class, 'getAppointmentByBarber']); // Listar citas de un barbero específico

// 📌 **PRODUCTOS (Products)**
Route::get('/products', [ProductController::class, 'index']); // Listar todos los productos
Route::get('/products-low-stock', [ProductController::class, 'getLowStockProducts']); // Listar productos con stock bajo

// 📌 **SERVICIOS (Services)**
Route::get('/servicios/{service}', [ServiceController::class, 'show']); //getServiciosById
Route::get('/servicios', [ServiceController::class, 'index']); //Obtener servicios

/* 📌 DESPACHOS (Dispatches) */
Route::get('/barber-dispatch', [BarberDispatchController::class, 'index']);
Route::get('/barber-dispatch/{dispatch}', [BarberDispatchController::class, 'show']);
Route::get('/dispatch-by-barber', [BarberDispatchController::class, 'getDispatchByBarber']);
Route::post('/barbers/report', [BarberController::class, 'calculateReport']);


// 📌 TIPS DE CUIDADO (Care Tips)
Route::get('/care-tips', [CareTipController::class, 'index']); // Listar todos los tips
Route::get('/care-tips/{careTip}', [CareTipController::class, 'show']); // Mostrar un tip específico
Route::post('/care-tips/by-services', [CareTipController::class, 'getTipsByServices']); // Obtener tips por servicios

// 📌 REPORTES (Reports)
Route::get('reports/daily-summary', [ReportController::class, 'dailySummary']);
Route::get('reports/yearly-income', [ReportController::class, 'yearlyIncomeByMonth']);
Route::get('reports/popular-services', [ServiceController::class, 'getPopularServices']);

// 📌 FACTURAS (Invoices)
Route::get('/invoices', [InvoiceController::class, 'index']); // Listar todas las facturas
