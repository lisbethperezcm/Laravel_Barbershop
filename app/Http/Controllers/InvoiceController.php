<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
use App\Services\InvoiceService;
use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceCollection;
use App\Http\Resources\InvoiceResource;
use App\Notifications\InvoiceGeneratedNotification;
use App\Models\Service;  // Asegúrate de importar el modelo de servicios si lo usas.

class InvoiceController extends Controller
{
    /**
     * Crear una factura a partir de la cita completada.
     *
     * @param  Appointment  $appointment
     * @return \Illuminate\Http\Response
     */
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    public function index()
    {
        $invoices = Invoice::with(['appointment', 'client.person', 'invoiceDetails.product', 'invoiceDetails.service'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'data' => new InvoiceCollection($invoices),
            'errorCode' => 200
        ], 200);
    }
    public function store(InvoiceRequest $request)
    {
        //Obtener el usuario autenticado 
        $user = auth()->user();

        $invoice = $this->invoiceService->createInvoice([
            'appointment_id' => $request->appointment_id ?? null,
            'client_id' => $request->client_id,
            'barber_id' => $request->barber_id,
            'status_id' => $request->status_id,
            'reference_number' => $request->reference_number,
            'aprovation_number' => $request->aprovation_number,
            'products' => $request->products,
            
        ]);

        // Enviar la notificación
    //    $user->notify(new InvoiceGeneratedNotification($invoice));

        $invoice = Invoice::with(['appointment', 'client.person', 'invoiceDetails.product', 'invoiceDetails.service'])
            ->find($invoice->id);

        // Retornar la respuesta
        return response()->json([
            'message' => 'Factura creada correctamente',
            'data' => new InvoiceResource($invoice)
        ], 201);
    }

    public function destroy(Invoice $invoice)
    {
        // Eliminar la factura
        $this->invoiceService->deleteInvoice($invoice);

        return response()->json([
            'message' => 'Factura eliminada correctamente',
        'errorCode' => 200
        ], 200);
    }
}