<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Person;
use Illuminate\Http\Request;
use App\Http\Resources\ClientCollection;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $clientsQuery = Client::with(['Person.user']);

           // Obtener el nombre del cliente del request (si viene)
        $client_name = $request->input('name');

        // Filtro por nombre completo si se envía en la petición
        if ($client_name) {
            $clientsQuery->whereHas('Person', function ($q) use ($client_name) {
                $q->fullNameLike($client_name);
            });
        }

        $clients = $clientsQuery->get();


         return response()->json([
            'data' => new   ClientCollection($clients),
            'errorCode' => '200'
        ], 200);
       
    }

    /*
     * Store a newly created resource in storage.
     */
    public function store(Person $person)
    {
        $client = new Client();
        // Eso relaciona el modelo cliente con el modelo persona 
        // asume que el campo person_id  en el modelo cliente se relaciona con el id persona
        $person->client()->save($client);
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {

        if ($client->person) {
            $client->person->delete(); // Soft Delete de la persona
        }
        if ($client->person->user) {
            $client->person->user->delete(); // Soft Delete del usuario
        }
        $client->delete(); // Soft Delete: solo marca deleted_at

        return response()->json([
            'message' => 'Cliente eliminado correctamente',
            'errorCode' => 200
        ]);
    }
}
