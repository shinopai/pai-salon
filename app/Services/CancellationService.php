<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Reservation;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Mail\CancellationCompletedMail;
use Illuminate\Support\Facades\Mail;

class CancellationService
{
    /**
     * 予約をキャンセルする。
     */
    public function cancel(Reservation $reservation): void
    {
        if ($reservation->status !== ReservationStatus::RESERVED) {
            throw ValidationException::withMessages([
                'reservation' => 'この予約はキャンセルできません。',
            ]);
        }

        $cancellationDeadline = $reservation->start_at
            ->copy()
            ->subDay()
            ->endOfDay();

        if (now()->greaterThan($cancellationDeadline)) {
            throw ValidationException::withMessages([
                'reservation' => 'キャンセル期限を過ぎているため、キャンセルできません。',
            ]);
        }

        DB::transaction(function () use ($reservation): void {
            $reservation->status = ReservationStatus::CANCELLED;
            $reservation->cancelled_at = now();
            $reservation->save();
        });

        Mail::to($reservation->customer_email)
            ->send(new CancellationCompletedMail($reservation->fresh()));
    }

    /**
     * トークンで予約をキャンセルする。
     */
    public function cancelByToken(
        string $reservationNumber,
        string $rawToken,
    ): void {
        $reservation = Reservation::query()
            ->where('reservation_number', $reservationNumber)
            ->first();

        if (
            $reservation === null
            || ! Hash::check($rawToken, $reservation->cancellation_token)
        ) {
            throw ValidationException::withMessages([
                'reservation' => '予約情報を確認できません。',
            ]);
        }

        $this->cancel($reservation);
    }
}
