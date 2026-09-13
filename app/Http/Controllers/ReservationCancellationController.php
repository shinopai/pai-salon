<?php

namespace App\Http\Controllers;

use App\Services\CancellationService;

class ReservationCancellationController extends Controller
{
    /**
     * キャンセル確認画面を表示する。
     */
    public function show(
        string $reservationNumber,
        string $token,
        CancellationService $cancellationService,
    ) {
        $reservation = $cancellationService->getReservationByToken(
            $reservationNumber,
            $token,
        );

        return view('reservations.cancel', [
            'reservation' => $reservation,
            'token' => $token,
        ]);
    }

    /**
     * 予約をキャンセルする。
     */
    public function cancel(
        string $reservationNumber,
        string $token,
        CancellationService $cancellationService,
    ) {
        $reservation = $cancellationService->cancelByToken(
            $reservationNumber,
            $token,
        );

        return view('reservations.cancel-complete', [
            'reservation' => $reservation,
        ]);
    }
}
