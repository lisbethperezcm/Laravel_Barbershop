<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceRequest;
use App\Models\Service;
use Illuminate\Http\Request;

use App\Models\User;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $services = Service::all(); // Obtiene todos los roles de la base de datos
        return response()->json($services); // Devuelve los roles en formato JSON
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store( ServiceRequest $request)
    {


        $service = new Service([
            'name'           => $request->name,
            'current_price'  => $request->current_price,
            'previous_price' => $request->previous_price,
            'duration'       => $request->duration,
           
        ]);

        $service->save();

        return response()->json($service, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        // Obtener los datos de la solicitud sin validación

        $service = Service::findOrFail($service->id);


        $validatedData = $request->all();

        // Actualizar la cita con los datos recibidos
        $service->update($validatedData);


        // Respuesta en formato JSON
        return response()->json([
            'success' => true,
            'message' => 'Servicio updated successfully',
            'data' => $service
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }
}
