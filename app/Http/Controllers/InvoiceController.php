<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
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
    public function storeFromAppointment(Appointment $appointment)
    {
        // Calcular el total y el impuesto de la cita
        $total = $this->calculateTotalForAppointment($appointment);
        $tax = $this->calculateTax($total);
      
        // Crear la factura
        $invoice = Invoice::create([
            'appointment_id' => $appointment->id,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'client_id' => $appointment->client_id,  // Relación de cliente
        ]);

        // Crear los detalles de la factura
        $this->createInvoiceDetails($invoice, $appointment);

        $user = User::find($appointment->client->person->user_id);
        
       $invoice->refresh();  // Asegúrate de que la factura esté actualizada
       $user->notify(new InvoiceGeneratedNotification($invoice));
// Enviar la notificación
      

        // Retornar la respuesta de éxito
        return response()->json([
            'message' => 'Invoice created successfully',
            'data' => $invoice
        ], 201);

      
    }

    /**
     * Calcular el total para la cita.
     *
     * @param  Appointment  $appointment
     * @return float
     */
    protected function calculateTotalForAppointment(Appointment $appointment)
    {
        // Calcular el total sumando los precios de los servicios de la cita
        return $appointment->services->sum('current_price');
    }

    /**
     * Calcular el impuesto basado en el total.
     *
     * @param  float  $total
     * @return float
     */
    protected function calculateTax($total)
    {
        // Suponiendo un impuesto del 18%
        return $total * 0.18;
    }

    /**
     * Crear los detalles de la factura a partir de los servicios de la cita.
     *
     * @param  Invoice  $invoice
     * @param  Appointment  $appointment
     * @return void
     */
    protected function createInvoiceDetails(Invoice $invoice, Appointment $appointment)
    {
       
    
        // Iterar sobre los servicios de la cita y crear un detalle de factura para cada uno
        foreach ($appointment->services as $service) {
            // Calcular el precio total del servicio considerando la cantidad de 1 por defecto
            $serviceTotal = $service->current_price; // Si quantity es 1 por defecto, el total es igual al precio
    
            // Crear un detalle de factura para cada servicio
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'service_id' => $service->id,
                'quantity' => 1,  // Establecer la cantidad a 1
                'price' => $service->current_price,
                'total' => $serviceTotal,  // Guardar el total calculado para este servicio
            ]);
    
            // Sumar el precio total de este servicio al total general de la factura
         
        }
    
    }
    

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        //
    }

   
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        //
    }
}
