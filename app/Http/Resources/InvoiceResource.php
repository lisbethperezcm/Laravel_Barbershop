<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\GeneralHelper;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
    
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'appointment_id' => $this->appointment_id,
            'client_name' => $this->client->person->first_name . ' ' . $this->client->person->last_name,
            'details' => $this->invoiceDetails->map(fn($detail) => [
            'type' => $detail->service_id ? 'service' : 'product',
            'name' => $detail->service_id ? $detail->service->name : $detail->product->name,
            'quantity' => $detail->quantity,
            'price' => $detail->price,
             'itbis' =>$detail->product_id 
                ? $this->GeneralHelper::getFloat( $detail->price * $detail->quantity * $detail->product->itbis): 0.00,
           
                'subtotal' => $this->GeneralHelper::getFloat($detail->price * $detail->quantity)   
    ]),
            'itbis' => $this->itbis,
            'subtotal'=>floatval($this->total - $this->itbis),
            'total' => $this->total,
           
            'created_at' => $this->created_at,
          
        ];
    }

     /**
     * Transform the resource into an array.
     *
     * @return float
    
     */


}
