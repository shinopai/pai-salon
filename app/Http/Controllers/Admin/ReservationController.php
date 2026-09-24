<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::query()
            ->orderBy('start_at')
            ->get();

        return view('admin.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation): View
    {
        return view('admin.reservations.show', compact('reservation'));
    }
}
