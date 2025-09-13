<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryEntry;
use App\Http\Controllers\Controller;
use App\Services\InventoryEntryService;
use App\Http\Requests\GetInventoryRequest;
use App\Http\Requests\InventoryEntryRequest;
use App\Http\Resources\InventoryEntryCollection;

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
    public function index(GetInventoryRequest $request)
    {
        $start    = $request->start_date ?? null;
        $end      = $request->end_date ?? null;
        $invoiceNumber = $request->invoice_number ?? null;

        $inventoryEntries = InventoryEntry::with(['createdBy.person', 'entryDetails.product'])
            ->dateRange($start, $end)
            ->invoiceNumber($invoiceNumber)
            ->orderBy('entry_date', 'desc')
            ->get();

        return response()->json([
            'data'      => new InventoryEntryCollection($inventoryEntries),
            'errorCode' => 200,
        ], 200);
    }

    /**
     * Crear una entrada de inventario.
     */
    public function store(InventoryEntryRequest $request) // o Request $request
    {
        $user = auth()->user();

        $inventoryEntry = $this->inventoryEntryService->createInventoryEntry([
            'entry_type' => $request->entry_type ?? 'Compra',
            'entry_date' => $request->entry_date,
            'invoice_number' => $request->invoice_number ?? null,
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
     * Update the specified resource in storage.
     */
    public function update(InventoryEntryRequest $request, InventoryEntry $inventoryEntry)
    {
        $user = auth()->user();

        $inventoryEntry = $this->inventoryEntryService->updateInventoryEntry($inventoryEntry, [

            'entry_date'    => $request->input('entry_date'),
            'invoice_number' => $request->input('invoice_number'),
            'note'           => $request->input('note'),
            'products'       => $request->has('products') ? $request->input('products') : null,

        ]);

        return response()->json([
            'message'   => 'Entrada de inventario actualizada exitosamente',
            'data'      => $inventoryEntry,
            'errorCode' => 200,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InventoryEntry $inventoryEntry) {

          // Eliminar la entrada de inventario
        $this->inventoryEntryService->deleteInventoryEntry($inventoryEntry);

        return response()->json([
            'message' => 'Entrada de inventario eliminada exitosamente',
            'errorCode' => 200
        ], 200);
    }
}
