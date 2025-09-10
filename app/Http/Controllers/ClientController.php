<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Person;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\ClientResource;
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
        $client_name = $request->input('name') ? trim($request->name) : null;

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
     
        //Retornar el Cliente
        return response()->json([
            'data' => new ClientResource($client),
            'errorCode' => '200'
        ], 200);
    

    }

    
    /**
     * Update the specified resource in storage.
     */
      public function update(Request $request)
    {
        // Cargar cliente con relaciones
        $client = Client::with(['person.user'])->findOrFail($request->input('client_id'));
        $person = $client->person;
        $user   = $person?->user;

        //Validación 
        $validated = $request->validate([
            'client_id'    => 'required|integer',
            // Person
            'first_name'      => 'sometimes|string|max:100|nullable',
            'last_name'       => 'sometimes|string|max:100|nullable',
            'document_number' => 'sometimes|string|max:20|nullable',
            'phone_number'    => 'sometimes|string|max:20|nullable',
            'address'         => 'sometimes|string|max:255|nullable',
            // User (Email)
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                $user ? Rule::unique('users', 'email')->ignore($user->id) : Rule::unique('users', 'email'),
            ],
            
        ]);

        //Separar datos por modelo
    
        $personKeys = ['first_name', 'last_name', 'document_number', 'phone_number', 'address'];
        $personData = Arr::only($validated, $personKeys);

        $userData   = Arr::only($validated, ['email']);

     

        //Actualizar modelos si hay datos
     
        if (!empty($personData) && $person) {
            $person->update($personData);
        }

        if (!empty($userData) && $user) {
            $user->update($userData);
        }

        //Respuesta con datos actualizados
        $client->refresh()->load(['person.user']);

        return response()->json([
            'data' => new ClientResource($client),
            'errorCode' => '200',
        ], 200);
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
