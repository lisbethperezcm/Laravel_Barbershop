<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ExitDetail;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\InventoryExit;
use function Laravel\Prompts\error;

use App\Services\InventoryExitService;
use App\Http\Requests\GetInventoryRequest;
use App\Http\Requests\InventoryExistRequest;
use App\Http\Resources\InventoryExitCollection;

class InventoryExitsController extends Controller
{
    protected $inventoryExitService;

    public function __construct(InventoryExitService $inventoryExitService)
    {
        $this->inventoryExitService = $inventoryExitService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(GetInventoryRequest $request)
    {
        $start    = $request->start_date ?? null;
        $end      = $request->end_date ?? null;
        // Obtener las salidas de inventario con los filtros aplicados
        $inventoryExits = InventoryExit::with(['createdBy.person', 'exitDetails.product']) // createdBy es opcional si lo tienes
            ->dateRange($start, $end)
            ->orderBy('exit_date', 'desc')
            ->get();

        return response()->json([
            'data'      => new InventoryExitCollection($inventoryExits),
            'errorCode' => 200,
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(InventoryExistRequest $request)
    {
        //Obtener el usuario autenticado 
        $user = auth()->user();

        // Crear la salida de inventario
        $inventoryExit = $this->inventoryExitService->createInventoryExit([
            'exit_type' => $request->exit_type,
            'exit_date' => $request->exit_date,
            'note' => $request->note ?? null,
            'products' => $request->products,
        ]);


        return response()->json([
            'message' => 'Salida de inventario creada exitosamente',
            'data' => $inventoryExit,
            'errorCode' => 201
        ], 201);
    }



    /**
     * Display the specified resource.
     */
    public function show(InventoryExit $inventoryExit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InventoryExit $inventoryExit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryExit $inventoryExit)
    {
        // Eliminar la salida de inventario
        $this->inventoryExitService->deleteInventoryExit($inventoryExit);

        return response()->json([
            'message' => 'Salida de inventario eliminada exitosamente',
            'errorCode' => 200
        ], 200);
    }
}
