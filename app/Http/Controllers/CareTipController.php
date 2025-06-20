<?php

namespace App\Http\Controllers;

use App\Models\CareTip;
use Illuminate\Http\Request;

class CareTipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $careTips = CareTip::with('service')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $careTips,
            'errorCode' => '200'
        ], 200); 
    }

     /**
        * Get care tips by service IDs.
     */
    public function getTipsByServices(Request $request)
{
    $validated = $request->validate([
        'services' => 'required|array',
        'services.*' => 'exists:services,id',
    ]);

    $careTips = CareTip::whereIn('service_id', $validated['services'])
        ->with('service')
        ->get();

    return response()->json([
        'data' => $careTips,
        'errorCode' => '200'
    ], 200);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $careTip = CareTip::create($validated);

        return response()->json([
            'message' => 'Tip de cuidado creado exitosamente.',
            'data' => $careTip,
            'errorCode' => '201'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(CareTip $careTip)
    {
         $careTip->load('service');

        return response()->json([
            'data' => $careTip,
            'errorCode' => '200'
        ], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CareTip $careTip)
    {
        
        $validated = $request->validate([
            'service_id' => 'sometimes|exists:services,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'updated_by' => 'nullable|exists:users,id',
        ]);

        $careTip->update($validated);

        return response()->json([
            'message' => 'Tip de cuidado actualizado con éxito.',
            'data' => $careTip,
            'errorCode' => '200'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CareTip $careTip)
    {
        $careTip->delete();

        return response()->json([
            'message' => 'Tip de cuidado eliminado con éxito.',
            'errorCode' => '200'
        ], 200);
    }
}
