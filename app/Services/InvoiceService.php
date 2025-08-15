<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesInventoryLines;

class InvoiceService
{
    use HandlesInventoryLines;

    public function createInvoice(array $data): Invoice
    {

        $appointmentId = $data['appointment_id'] ? : null;
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;
        $products = $data['products'] ?? [];
        // Asignar el id del cliente si se obtiene de la cita o se obtiene de fomulario
        $client_id = $appointment ? $appointment->client_id : $data['client_id'];
        // 1) Crear la factura sin total
        $invoice = Invoice::create([
            'appointment_id' => $appointment?->id ,
            'client_id' => $client_id,
            'barber_id' => $appointment?->barber_id,
            'status_id' => $data['status_id'],
            'reference_number' => $data['reference_number'],
            'aprovation_number' => $data['aprovation_number'],
            'payment_type_id' => null,
            'total' => 0, // se recalcula luego
            'itbis' => 0, // se recalcula luego
        ]);
        
        // 2) Sincronizar detalles (resta stock: -1)
        $this->processProductDetails(
            movement: $invoice,
            productLines: $products,
            detailsRelation: 'invoiceDetails',
            stockDirection: -1, // resta stock
            getUnitCost: fn($product, $line) => $product->sale_price, // usa el unit_cost de la línea o del producto si no viene
            priceColumn: 'price' // columna de precio en InvoiceDetail
        );

        $this->createServiceDetailsFromAppointment($invoice, $appointment);
        // Calcular subtotales y ITBIS
        $servicesSubtotal = $appointment ? $this->getServicesSubtotal($appointment) : 0;
        $productsSubtotal = $this->getProductsSubtotal($products);
        $taxAmount = $this->getProductsTaxAmount($products);

        //Calcular el total y actualizar la factura 
        $total = $servicesSubtotal + $productsSubtotal + $taxAmount;
        $invoice->update([
            'total' => $total,
            'itbis' => $taxAmount,
        ]);

        return $invoice;
    }

protected function createServiceDetailsFromAppointment(Invoice $invoice, Appointment $appointment): void
{
    // Asegura tener los servicios cargados
    $appointment->loadMissing('services');

    foreach ($appointment->services as $service) {
        $quantity = (int) ($service->pivot->quantity ?? 1);
        if ($quantity < 1) { $quantity = 1; }

        $invoice->invoiceDetails()->create([
            'service_id' => $service->id,
            'product_id' => null,
            'quantity'   => $quantity,
            //"precio unitario" de venta del servicio.
            'price'  => number_format((float)$service->current_price, 2, '.', ''),
        
        ]);
    }
}
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
}
