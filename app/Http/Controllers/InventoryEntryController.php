<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryEntry;
use App\Http\Controllers\Controller;
use App\Services\InventoryEntryService;
use App\Http\Requests\InventoryEntryRequest;

class InventoryEntryController extends Controller
{
    protected InventoryEntryService $inventoryEntryService;

    public function __construct(InventoryEntryService $inventoryEntryService)
    {
        $this->inventoryEntryService = $inventoryEntryService;
    }

    /**
     * Listado de entradas de inventario.
     */
    public function index()
    {
        $inventoryEntries = InventoryEntry::with(['createdBy.person', 'entryDetails.product']) // createdBy es opcional si lo tienes
            ->orderBy('entry_date', 'desc')
            ->get();

        return response()->json([
            'data'      => $inventoryEntries,
            'errorCode' => 200,
        ], 200);
    }

    /**
     * Crear una entrada de inventario.
     */
    public function store(InventoryEntryRequest $request) // o Request $request
    {
        $user = auth()->user(); // si luego quieres registrar created_by

        $inventoryEntry = $this->inventoryEntryService->createInventoryEntry([
            'entry_type' => $request->entry_type ?? 'Compra',
            'entry_date' => $request->entry_date,
            'note'       => $request->note ?? null,
            'products'   => $request->input('products', []), // siempre array
        ]);

        return response()->json([
            'message'   => 'Entrada de inventario creada exitosamente',
            'data'      => $inventoryEntry,
            'errorCode' => 201,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
