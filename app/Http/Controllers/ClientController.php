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
    public function index()
    {
       $clients = Client::with(['Person.user'])->get();

       return new ClientCollection($clients);
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
        //
    }
}
