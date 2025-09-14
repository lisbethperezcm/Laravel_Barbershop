<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Appointment;
use App\Models\BarberReview;
use Illuminate\Http\Request;
use App\Http\Resources\BarberReviewCollection;

class BarberReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $barberId = $request->input('barber_id');

        $reviews = BarberReview::with(['client.person', 'barber.person'])
            ->when($barberId, function ($query, $barberId) {
                $query->where('barber_id', $barberId);
            })
            ->latest('created_at')
            ->get();

        return response()->json([
            'data' => new BarberReviewCollection($reviews),
            'errorCode' => '200'
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReviewRequest $request)
    {

        // Verificar que la cita exista
        $appointment = Appointment::find($request->input('appointment_id'));

        $review = new BarberReview([
            'client_id'      => $request->input('client_id'),
            'barber_id'      => $appointment->barber_id,
            'appointment_id' => $request->input('appointment_id'),
            'rating'         => $request->input('rating'),
            'comment'        => $request->input('comment') ?? null,
        ]);

        $review->save();

        return response()->json([
            'message' => 'Reseña creada exitosamente.',
            'data'    => $review->load(['client.person', 'barber.person']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
