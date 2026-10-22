<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    public function index()
    {
        $reservations = Reservation::with('room')->latest()->paginate(20);

        return view('admin.reservations', compact('reservations'));
    }

    public function updateStatus(
        UpdateReservationStatusRequest $request,
        Reservation $reservation,
        ReservationService $reservations
    )
    {
        $reservations->updateStatus($reservation, $request->validated('status'));

        return back()->with('status', 'Reservation updated.');
    }
}
