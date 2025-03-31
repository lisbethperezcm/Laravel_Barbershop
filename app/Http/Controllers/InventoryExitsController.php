<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryExistRequest;
use App\Models\Product;
use App\Models\ExitDetail;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Models\InventoryExit;

use function Laravel\Prompts\error;
use App\Services\InventoryExitService;

class InventoryExitsController extends Controller
{
    protected $inventoryExitService;

    public function __construct(InventoryExitService $inventoryExitService)
    {
        $this->inventoryExitService = $inventoryExitService;
    }
    public function index() {}


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
     * Show the form for editing the specified resource.
     */
    public function edit(InventoryExit $inventoryExit)
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
        //
    }
}
