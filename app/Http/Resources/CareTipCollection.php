<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CareTipCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->map(function ($careTip) {
            return [
                'id' => $careTip->tip_id ?? $careTip->id,
                'service_id' => $careTip->service_id,
                'service_name' => $careTip->service ? $careTip->service->name : null,
                'name' => $careTip->name,
                'description' => $careTip->description,
            ];
        });
    }
}