<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use App\Http\Requests\StaffReservationUpdateRequest;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;

class StaffReservationController extends Controller
{
    public function index(): View
    {
        $staff = Auth::user()->staff;

        $reservations = Reservation::where('staff_id', $staff->id)
            ->get();

        return view('staff.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation): View
    {
        return view('staff.reservations.show', compact('reservation'));
    }

    public function update(
        StaffReservationUpdateRequest $request,
        Reservation $reservation,
        ReservationService $reservationService
    ): RedirectResponse {
        $reservationService->update(
            $reservation,
            $request->validated()
        );

        return redirect()->route(
            'staff.reservations.show',
            $reservation
        );
    }
}
