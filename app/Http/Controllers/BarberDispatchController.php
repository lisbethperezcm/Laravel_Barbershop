<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use App\Models\InventoryExit;
use App\Models\BarberDispatch;
use Illuminate\Support\Facades\DB;
use App\Services\InventoryExitService;
use App\Http\Resources\BarberDispatchResource;
use App\Http\Resources\BarberDispatchCollection;

class BarberDispatchController extends Controller
{
    protected $inventoryExitService;

    public function __construct(InventoryExitService $inventoryExitService)
    {
        $this->inventoryExitService = $inventoryExitService;
    }

    public function index()
    {
        $dispatches = BarberDispatch::with('barber')->get();


        //Retornar el listado de despachos formateada con AppointmentCollection
        return response()->json([
            'data' => new  BarberDispatchCollection($dispatches),
            'errorCode' => '200'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        //Obtener el usuario autenticado 
        $user = auth()->user();

        // Validar la solicitud
        $request->validate([
            'barber_id' => 'required|exists:barbers,id',
            'dispatch_date' => 'required|date',
            'status_id' => 'required|exists:statuses,id',
            'products' => 'required|array',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);


        $barber = Barber::findOrFail($request->barber_id);
        // 👇 Obtener ingreso neto del barbero en el mes
        $netIncome = $barber->getCurrentMonthNetIncome();


        // 1) Crear la salida de inventario con el servicio (usa los campos que tu servicio espera)
        $inventoryExit = $this->inventoryExitService->createInventoryExit([
            'exit_type' => $request->input('exit_type', 'Despacho a Barbero'),
            'exit_date' => $request->input('dispatch_date'),
            'note'      => $request->input('note'),
            'products'  => $request->input('products', []), // formato: [{product_id, quantity}, ...]
        ]);


        $totalToDispatch = $inventoryExit->total;
        // Validar
        /*    if ($totalToDispatch > $netIncome) {
                return response()->json([
                    'message' => 'El monto a despachar excede el ingreso neto del barbero en este mes.',
                    'errorCode' => 422
                ], 422);
            }*/
        // 2) Crear el despacho del barbero vinculado a la salida creada
        $dispatch = BarberDispatch::create([
            'barber_id' => $request->input('barber_id'),
            'exit_id'   => $inventoryExit->id,
            'dispatch_date' => $request->input('dispatch_date'),
            'note'      => $request->input('note'),
            'status_id' => $request->input('status_id'),
            'created_by' => auth()->id(),
        ]);


        // 3) Respuesta simple
        return response()->json([
            'message'        => 'Despacho creado correctamente.',
            'data'       => new BarberDispatchResource($dispatch),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(BarberDispatch $dispatch)
    {
        //Consultar el despacho uniendo las relaciones de los otros modelos
        $dispatch = BarberDispatch::with(['barber'])->findOrFail($dispatch->id);

        //Retornar el despacho 
        return response()->json([
            'data' => new BarberDispatchResource($dispatch),
            'errorCode' => '200'
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BarberDispatch $dispatch, Request $request)
    {
        //Obtener el usuario autenticado 
        $user = auth()->user();

        $request->validate([
            'dispatch_date' => 'nullable|date',
            'status_id' => 'nullable|exists:statuses,id',
        ]);

        // Obtener el modelo InventoryExit lanza automáticamente un 404 si no existe
        $exit = InventoryExit::findOrFail($dispatch->exit_id);


        $validatedData = $request->all();

        // Actualizar la cita con los datos recibidos
        $dispatch->update($validatedData);


        // Llamar al servicio para actualizar los productos de la salida
        $updatedExit =  $this->inventoryExitService->updateInventoryExit($exit, (array) $validatedData);

        // Devolver la registro actualizado
        return response()->json([
            'message' => 'Despacho actualizado con éxito.',
            'data' => $dispatch,
            'errorCode' => '200'
        ], 200);
    }





    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
