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
        $invoices = Invoice::with(['appointment', 'client.person' => fn($q) => $q->withTrashed(),'barber.person' => fn($q) => $q->withTrashed(), 'invoiceDetails.product', 'invoiceDetails.service'])
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
            'payment_type_id' => $request->payment_type_id,
            'reference_number' => $request->reference_number,
            'aprovation_number' => $request->aprovation_number,
            'products' => $request->products,

        ]);




        $invoice = Invoice::with(['appointment', 'client.person' => fn($q) => $q->withTrashed(), 'barber.person' => fn($q) => $q->withTrashed(), 'invoiceDetails.product', 'invoiceDetails.service'])
            ->find($invoice->id);
    // Enviar la notificación
       $invoice->client->person->user?->notify(new InvoiceGeneratedNotification($invoice));

        // Retornar la respuesta
        return response()->json([
            'message' => 'Factura creada correctamente',
            'data' => new InvoiceResource($invoice),
        ], 201);
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {

        $user = auth()->user();
        $invoice = $this->invoiceService->updateInvoice($invoice, [
            'appointment_id' => $request->input('appointment_id'),
            'client_id' => $request->input('client_id'),
            'barber_id' => $request->input('barber_id'),
            'status_id' => $request->input('status_id'),
            'payment_type_id' => $request->input('payment_type_id'),
            'reference_number' => $request->input('reference_number'),
            'aprovation_number' => $request->input('aprovation_number'),
            'products' => $request->has('products') ? $request->input('products') : null,
            'services' => $request->has('services') ? $request->input('services') : null,
        ]);

        return response()->json([
            'message'   => 'Factura actualizada exitosamente',
            'data' => new InvoiceResource($invoice),
            'errorCode' => 200
        ], 200);
    }

    /**
     * Eliminar una factura.
     *
     * @param  Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
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
