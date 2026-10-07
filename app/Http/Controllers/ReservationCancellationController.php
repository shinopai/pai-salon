<?php

namespace App\Http\Controllers;

use App\Services\CancellationService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;

class ReservationCancellationController extends Controller
{
    public function show(
        string $reservationNumber,
        string $token,
        CancellationService $cancellationService,
    ): View {
        try {
            $reservation = $cancellationService->getReservationByToken(
                $reservationNumber,
                $token,
            );
        } catch (ValidationException) {
            return view('reservations.cancel-error');
        }

        return view('reservations.cancel', [
            'reservation' => $reservation,
            'token' => $token,
        ]);
    }

    public function cancel(
        string $reservationNumber,
        string $token,
        CancellationService $cancellationService,
    ): View {
        try {
            $reservation = $cancellationService->cancelByToken(
                $reservationNumber,
                $token,
            );
        } catch (ValidationException) {
            return view('reservations.cancel-error');
        }

        return view('reservations.cancel-complete', [
            'reservation' => $reservation,
        ]);
    }
}
