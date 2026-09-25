<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminReservationUpdateRequest;
use App\Models\Menu;
use App\Models\Reservation;
use App\Models\Staff;
use App\Services\ReservationService;
use Illuminate\Http\RedirectResponse;
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
        $staffs = Staff::query()
            ->orderBy('name')
            ->get();

        $menus = Menu::query()
            ->orderBy('name')
            ->get();

        return view('admin.reservations.show', compact(
            'reservation',
            'staffs',
            'menus',
        ));
    }

    public function update(
        AdminReservationUpdateRequest $request,
        Reservation $reservation,
        ReservationService $reservationService,
    ): RedirectResponse {
        $reservationService->update(
            $reservation,
            $request->validated(),
        );

        return redirect()
            ->route('admin.reservations.show', $reservation)
            ->with('success', '予約を更新しました。');
    }
}
