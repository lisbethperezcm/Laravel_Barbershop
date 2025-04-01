<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Helpers\GeneralHelper;

class BarberReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'barber_name' => $this->barber_name,
            'total_services' => GeneralHelper::getFloat($this->total_services),
            'commission_percentage' => GeneralHelper::getFloat($this->commission_percentage),
            'total_commission' => GeneralHelper::getFloat($this->total_commission),
            'net_income' => GeneralHelper::getFloat($this->net_income),
            'total_dispatches' => GeneralHelper::getFloat($this->total_dispatches),
            'final_balance' => GeneralHelper::getFloat($this->final_balance),

            'invoices' => $this->invoices->map(fn($invoice) => [
                'invoice_id' => $invoice->id,
                'date' => Carbon::parse($invoice->created_at)->format('d/m/Y'),
                'client_name' => $invoice->client->person->first_name. ' ' . $invoice->client->person->last_name,
                'total' => GeneralHelper::getFloat($invoice->total),
                'itbis' => GeneralHelper::getFloat($invoice->itbis),
                'status' => $invoice->status->name
            ]),

            'dispatches' => $this->dispatches->map(fn($dispatch) => [
                'dispatch_id' => $dispatch->id,
                'date' => Carbon::parse($dispatch->dispatch_date)->format('d/m/Y'),
                'products' => $dispatch->inventoryExit->exitDetails->map(fn($detail) => [
                    'product' => $detail->product->name,
                    'quantity' => $detail->quantity,
                    'unit_cost' => GeneralHelper::getFloat($detail->unit_cost),
                    'sub_total' => GeneralHelper::getFloat($detail->quantity * $detail->unit_cost),
                ]),
                'total' => GeneralHelper::getFloat($dispatch->inventoryExit->total),
                'status' => $dispatch->status->name
            ])
        ];
    }
}
