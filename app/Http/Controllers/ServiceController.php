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
        $services = Service::all(); // Obtiene todos los servicios de la base de datos

        return response()->json([
            'data' => $services,
            'errorCode' => '200'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {

        $service = new Service([
            'name'           => $request->name,
            'current_price'  => $request->current_price,
            'previous_price' => $request->previous_price,
            'duration'       => $request->duration,

        ]);

        $service->save();

        return response()->json([
            'message' => 'Servicio creado exitosamente.',
            'data' => $service,
            'errorCode' => '201'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        return response()->json([
            'data' => $service,
            'errorCode' => '200'
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        // Obtener los datos de la solicitud sin validación

        $service = Service::findOrFail($service->id);


        $validatedData = $request->all();

        // Actualizar el servicio con los datos recibidos
        $service->update($validatedData);


        // Respuesta en formato JSON
        return response()->json([
            'message' => 'Servicio actualizado con éxito',
            'data' => $service,
             'errorCode' => '200'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        $service->delete(); // Soft Delete: solo marca deleted_at

        return response()->json([
            'message' => 'Servicio eliminado correctamente',
            'errorCode' => 200
        ]);
    }
}
