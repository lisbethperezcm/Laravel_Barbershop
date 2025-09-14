<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Appointment;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use App\Traits\HandlesInventoryLines;

class InvoiceService
{
    use HandlesInventoryLines;

    public function createInvoice(array $data): Invoice
    {

        $appointmentId = $data['appointment_id'] ?: null;
        $appointment = $appointmentId ? Appointment::find($appointmentId) : null;
        $products = $data['products'] ?? [];
        // Asignar el id del cliente si se obtiene de la cita o se obtiene de fomulario
        $client_id = $appointment ? $appointment->client_id : $data['client_id'];
        // 1) Crear la factura sin total
        $invoice = Invoice::create([
            'appointment_id' => $appointment?->id,
            'client_id' => $client_id,
            'barber_id' => $appointment?->barber_id,
            'status_id' => $data['status_id'],
            'reference_number' => $data['reference_number'] ?? null,
            'aprovation_number' => $data['aprovation_number'] ?? null,
            'payment_type_id' => $data['payment_type_id'] ?? null,
            'total' => 0, // se recalcula luego
            'itbis' => 0, // se recalcula luego
        ]);

        // Normalizar las líneas de productos para usar 'product_id'
        $products01 = collect($products)
            ->map(function ($line) {
                if (isset($line['id']) && !isset($line['product_id'])) {
                    $line['product_id'] = $line['id'];
                }
                return $line;
            })
            ->all();


        //Sincronizar detalles (resta stock: -1)
        $this->processProductDetails(
            movement: $invoice,
            productLines: $products01,
            detailsRelation: 'invoiceDetails',
            stockDirection: -1, // resta stock
            getUnitCost: fn($products, $line) => $products->sale_price, // usa el unit_cost de la línea o del producto si no viene
            priceColumn: 'price' // columna de precio en InvoiceDetail
        );

        if ($appointmentId) {
            // 3) Crear detalles de servicios desde la cita
            $this->createServiceDetailsFromAppointment($invoice, $appointment);
        }


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

    /**
     * Actualizar una salida de inventario con detalles de productos.
     */
    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {

        $products = array_key_exists('products', $data) ? $data['products'] : null;

        $invoice->update([
            'appointment_id'    => $data['appointment_id']    ?? $invoice->appointment_id,
            'client_id'         => $data['client_id']         ?? $invoice->client_id,
            'barber_id'         => $data['barber_id']         ?? $invoice->barber_id,
            'status_id'         => $data['status_id']         ?? $invoice->status_id,
            'reference_number'  => $data['reference_number']  ?? $invoice->reference_number,
            'aprovation_number' => $data['aprovation_number'] ?? $invoice->aprovation_number,
            'payment_type_id'   => $data['payment_type_id']   ?? $invoice->payment_type_id,
        ]);

        // Si no vienen productos, no tocamos detalles ni totales
        if (isset($products)) {
            $products = $data['products'] ?? [];
            // Normalizar SOLO para el trait (id -> product_id)
            $products01 = collect($data['products'])
                ->map(function ($line) {
                    if (isset($line['id']) && !isset($line['product_id'])) {
                        $line['product_id'] = $line['id'];
                    }
                    return $line;
                })
                ->all();

            // Sincronizar detalles con el trait usando SOLO $products01
            $this->processProductDetails(
                movement: $invoice,
                productLines: $products01 ?? [],
                detailsRelation: 'invoiceProductDetails', // solo productos
                stockDirection: -1,
                getUnitCost: fn($products, $line) => $products->sale_price, // correcto: $products
                priceColumn: 'price'
            );
        }

        $services = $data['services'] ?? null;
        if ($services) {

            $this->updateServiceDetailsFromArray($invoice, $services);

            // Si se actualizaron servicios, recalcular subtotal de servicios desde DB
            $servicesSubtotal = $this->getServicesSubtotalFromInvoiceQuery($invoice);
        } else {

            $appointmentId    = $data['appointment_id'] ?? $invoice->appointment_id;
            $appointment      = $appointmentId ? Appointment::find($appointmentId) : null;
            $servicesSubtotal = $appointment ? $this->getServicesSubtotal($appointment) : 0.0;
        }
        // Recalcular totales 

        $productsSubtotal = $products ? $this->getProductsSubtotal($products) : 0.0;
        $taxAmount        = $products ? $this->getProductsTaxAmount($products) : $invoice->itbis;

        $total = $servicesSubtotal + $productsSubtotal + $taxAmount;

        $invoice->update([
            'total' => $total,
            'itbis' => $taxAmount,
        ]);

        return $invoice->fresh(['invoiceDetails.product', 'invoiceDetails.service']);
    }
    /**
     * Crear detalles de servicios en la factura basados en la cita.
     */
    protected function createServiceDetailsFromAppointment(Invoice $invoice, Appointment $appointment): void
    {
        // Asegura tener los servicios cargados
        $appointment->loadMissing('services');

        foreach ($appointment->services as $service) {
            $quantity = (int) ($service->pivot->quantity ?? 1);
            if ($quantity < 1) {
                $quantity = 1;
            }

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

    /**
     * Crear detalles de servicios en la factura a partir de un array de servicios.
     *
     * @param Invoice $invoice
     * @param array   $services [['service_id' => int, 'quantity' => int, 'price' => float]]
     */
    protected function updateServiceDetailsFromArray(Invoice $invoice, array $services): void
    {
        $serviceIdsInPayload = collect($services)->pluck('service_id')->filter()->values();

        // Crear o actualizar
        foreach ($services as $service) {

            $existingPrice = $invoice->invoiceDetails()
                ->where('service_id', $service['service_id'])
                ->whereNull('product_id')
                ->value('price');

            $incomingPrice = $service['price'] ?? null;

            $price = ($incomingPrice !== null)
                ? (float) $incomingPrice
                : (float) ($existingPrice ?? Service::where('id', $service['service_id'])->value('current_price'));

            $invoice->invoiceDetails()->updateOrCreate(
                [
                    'service_id' => $service['service_id'],
                    'product_id' => null,
                ],
                [
                    'quantity' => $service['quantity'],
                    'price'    => number_format($price, 2, '.', ''),
                ]
            );
        }

        // Eliminar los que ya no vinieron
        if ($serviceIdsInPayload->isNotEmpty()) {
            $invoice->invoiceDetails()
                ->whereNotNull('service_id')
                ->whereNotIn('service_id', $serviceIdsInPayload)
                ->delete();
        } else {
            $invoice->invoiceDetails()
                ->whereNotNull('service_id')
                ->delete();
        }
    }


    /**
   
     * Calcular el subtotal de servicios a partir de un array.
     *
     * @param array $services [['service_id' => int, 'quantity' => int, 'price' => float]]
     * @return float
     */
    /**
     * Subtotal de servicios consultando directamente en DB (evita problemas de no tener la relación cargada).
     */
    protected function getServicesSubtotalFromInvoiceQuery(Invoice $invoice): float
    {
        $details = $invoice->invoiceDetails()
            ->whereNotNull('service_id')
            ->get(['price', 'quantity']);

        $total = $details->sum(fn($d) => ((float)$d->price) * ((int)$d->quantity));

        return round((float)$total, 2);
    }

    /**
     * Obtener el subtotal basado en los productos (los servicios no llevan subtotal).
     */
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
        if (empty($products)) return 0.0;

        // Acepta 'product_id' o 'id'
        $ids = collect($products)
            ->map(fn($p) => $p['product_id'] ?? $p['id'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $productsModel = Product::whereIn('id', $ids)->get()->keyBy('id');

        return collect($products)->sum(function ($p) use ($productsModel) {
            $pid   = $p['product_id'] ?? $p['id'] ?? null;
            $qty   = (int) ($p['quantity'] ?? 1);
            $model = $pid ? $productsModel->get($pid) : null;

            return $model ? ((float) $model->calculated_itbis * $qty) : 0.0;
        });
    }

    /**
     * Elimina una factura y revierte el stock de sus detalles.
     */
    public function deleteInvoice(Invoice $invoice): Invoice
    {
        // Revertir stock de los detalles
        $this->softDeleteMovementAndRevertStock(
            $invoice,
            'invoiceProductDetails',
            -1 // Suma stock
        );

        return $invoice->fresh();
    }
}
