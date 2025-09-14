<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Helpers\GeneralHelper;

class InvoiceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($invoice) {
            return [
                'id'             => $invoice->id,
                'appointment_id' => $invoice->appointment_id,
                'status'         => $invoice->status?->name,
                'client_name'    => trim(
                    ($invoice->client->person->first_name ?? '') . ' ' . ($invoice->client->person->last_name ?? '')
                ),
                'payment_type' => $invoice->paymentType?->name,
                'details' => $invoice->invoiceDetails->map(function ($detail) {
                    $isService = !is_null($detail->service_id);

                    return [
                        'type'     => $isService ? 'service' : 'product',
                        'name'     => $isService
                            ? ($detail->service->name ?? null)
                            : ($detail->product->name ?? null),
                        'quantity' => $detail->quantity,
                        'price'    => $detail->price,
                        'itbis'    => $detail->product_id
                            ? (float) (($detail->product->calculated_itbis ?? 0) * $detail->quantity)
                            : 0.00,
                        'subtotal' => GeneralHelper::getFloat(($detail->price ?? 0) * ($detail->quantity ?? 0)),
                    ];
                })->values(), // limpia keys
                'itbis'     => (float) $invoice->itbis,
                'subtotal'  => GeneralHelper::getFloat(($invoice->total ?? 0) - ($invoice->itbis ?? 0)),
                'total'     => GeneralHelper::getFloat($invoice->total ?? 0),
                'created_at' => $invoice->created_at,
            ];
        });
    }
}
