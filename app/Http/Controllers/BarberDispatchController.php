<?php

namespace App\Http\Controllers;

use App\Models\Barber;
use Illuminate\Http\Request;
use App\Models\InventoryExit;
use App\Models\BarberDispatch;
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

        // Crear la salida de inventario usando el `Service`
        $inventoryExit = $this->inventoryExitService->createInventoryExit([
            'exit_type' => 'Despacho a Barbero',
            'exit_date' => $request->dispatch_date,
            'products' => $request->products,
            'note' => $request->note ?? null,
        ]);


        // Crear el despacho del barbero y asociarlo a la salida de inventario
        $dispatch = BarberDispatch::create([
            'exit_id' => $inventoryExit->id,
            'barber_id' => $request->barber_id,
            'dispatch_date' => $request->dispatch_date,
            'status_id' => $request->status_id,
        ]);


        return response()->json([
            'message' => 'Despacho registrado exitosamente.',
            'data' => $dispatch,
            'errorCode' => 201
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
