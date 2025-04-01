<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\InvoiceDetail;
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
    public function store(Request $request)
    {
        //Obtener el usuario autenticado 
        $user = auth()->user();

        $request->validate([
            'appointment_id' => 'nullable|exists:appointments,id',
            'client_id' => 'required_without:appointment_id|exists:clients,id',
            'products' => 'nullable|array',
            'products.*.id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'required_with:products|integer|min:1',
        ]);

        $appointment = $request->filled('appointment_id') ? Appointment::findOrFail($request->appointment_id) : null;

        // Asignar el id del cliente si se obtiene de la cita o se obtiene de fomulario
        $client_id = $appointment ? $appointment->client_id : $request->client_id;


        if (!$appointment && empty($request->products)) {
            return response()->json([
                'message' => 'Debe proporcionar una cita o al menos un producto.'
            ], 400);
        }

        $products = $request->input('products', []);

        // Calcular subtotales y ITBIS
        $servicesSubtotal = $appointment ? $this->getServicesSubtotal($appointment) : 0;
        $productsSubtotal = $this->getProductsSubtotal($products);
        $taxAmount = $this->getProductsTaxAmount($products);



        /*
        $invoice = new Invoice();
        $invoice->appointment_id = $appointment?->id;
        $invoice->client_id = $client_id;
        $invoice->total = $servicesSubtotal + $productsSubtotal + $taxAmount;
        $invoice->itbis = $taxAmount;
        $invoice->status_id = $request->status_id;
        $invoice->reference_number = $request->reference_number;
        $invoice->aprovation_number = $request->aprovation_number ?? 'N/A'; // 🔹 Asignar valor si no se envía
        $invoice->save();*/


        // Crear la factura
        $invoice = Invoice::create([
            'appointment_id' => $appointment?->id,
            'client_id' => $client_id,
            'barber_id' => $appointment?->barber_id, // Asigna el barbero si hay cita
            'total' => $servicesSubtotal + $productsSubtotal + $taxAmount,
            'itbis' => $taxAmount,
            'status_id' => $request->status_id,
            'reference_number' => $request->reference_number,
            'aprovation_number' =>  $request->aprovation_number,
            'payment_type_id' =>null,

        ]);




        // Crear los detalles de la factura
        $this->storeInvoiceDetails($invoice, $appointment, $products);




        //$user = User::find($appointment->client->person->user_id);

        // $invoice->refresh(); 
        // Enviar la notificación
        // $user->notify(new InvoiceGeneratedNotification($invoice));

        $invoice = Invoice::with(['appointment', 'client.person', 'invoiceDetails.product', 'invoiceDetails.service'])
            ->find($invoice->id);

        // Retornar la respuesta
        return response()->json([
            'message' => 'Factura creada correctamente',
            'data' => new InvoiceResource($invoice)
        ], 201);
    }

    /**
     * Calcular el total para la cita.
     *
     * @param  Appointment  $appointment
     * @return float
     */



    protected function getServicesSubtotal(Appointment $appointment): float
    {
        // Calcular el total sumando los precios de los servicios de la cita
        return $appointment->services->sum('current_price');
    }

    protected function getProductsSubtotal(?array $products): float
    {

        $productsDB = Product::whereIn('id', collect($products)->pluck('id'))->get()->keyBy('id');

        $total = 0;

        foreach ($products as $product) {
            if (isset($productsDB[$product['id']])) {
                $total += $productsDB[$product['id']]->sale_price * $product['quantity'];
            }
        }

        return $total;
    }

    /**
     * Obtener el ITBIS basado en los productos (los servicios no llevan ITBIS).
     */
    protected function getProductsTaxAmount(?array $products): float
    {
        if (empty($products)) {
            return 0;
        }
    
        // Cargar todos los productos en una sola consulta
        $productsModel = Product::whereIn('id', collect($products)->pluck('id'))->get()->keyBy('id');
    
        // Calcular el ITBIS sumando el total de ITBIS de cada producto usando el accesorio en el modelo
        return collect($products)->sum(
            fn($product) => ($productsModel[$product['id']]->calculated_itbis * $product['quantity'] ?? 0)
        );
    }


    //Crea el detalle de la factura en InvoiceDetails  
    protected function storeInvoiceDetails(Invoice $invoice, ?Appointment $appointment, ?array $products): void
    {
        // Agregar servicios si hay una cita
        if (isset($appointment)) {
            foreach ($appointment->services as $service) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'service_id' => $service->id,
                    'product_id' => null,
                    'quantity' => 1,
                    'price' => $service->current_price,
                    //'total' => $service->current_price,
                ]);
            }
        }

        // Agregar productos si hay
        if (isset($products)) {
            foreach ($products as $item) {
                $product = Product::find($item['id']);
                if ($product) {
                    InvoiceDetail::create([
                        'invoice_id' => $invoice->id,
                        'service_id' => null,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->sale_price,
                        //'total' => ($product->sale_price * $item['quantity']) + ($product->itbis * $item['quantity']),
                    ]);
                }
            }
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
