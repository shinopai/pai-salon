<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Staff;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\ReservationRequest;
use App\Services\ReservationService;

class ReservationController extends Controller
{
    public function menu()
    {
        $menus = Menu::all();

        return view('reservations.menu', compact('menus'));
    }

    public function staff(Request $request)
    {
        $menu = Menu::findOrFail($request->integer('menu_id'));

        $staffs = $menu->staffs;

        return view('reservations.staff', compact('menu', 'staffs'));
    }

    public function date(Request $request)
    {
        $menu = Menu::findOrFail($request->integer('menu_id'));
        $staff = Staff::findOrFail($request->integer('staff_id'));

        return view('reservations.date', compact('menu', 'staff'));
    }

    public function slots(Request $request, AvailabilityService $availabilityService)
    {
        $menuId = $request->integer('menu_id');
        $staffId = $request->integer('staff_id');
        $date = Carbon::parse($request->input('date'));

        $slots = $availabilityService->getAvailableSlots(
            $menuId,
            $staffId,
            $date
        );

        return view('reservations.slots', compact(
            'menuId',
            'staffId',
            'date',
            'slots'
        ));
    }

    public function customer(Request $request)
    {
        $menu = Menu::findOrFail($request->integer('menu_id'));
        $staff = Staff::findOrFail($request->integer('staff_id'));
        $date = Carbon::parse($request->input('date'));
        $startAt = Carbon::parse($request->input('start_at'));

        return view('reservations.customer', compact(
            'menu',
            'staff',
            'date',
            'startAt'
        ));
    }

    public function confirm(Request $request)
    {
        $menu = Menu::findOrFail($request->integer('menu_id'));
        $staff = Staff::findOrFail($request->integer('staff_id'));
        $date = Carbon::parse($request->input('date'));
        $startAt = Carbon::parse($request->input('start_at'));
        $customerName = $request->input('customer_name');
        $customerEmail = $request->input('customer_email');

        return view('reservations.confirm', compact(
            'menu',
            'staff',
            'date',
            'startAt',
            'customerName',
            'customerEmail'
        ));
    }

    public function store(
        ReservationRequest $request,
        ReservationService $reservationService
    ) {
        $reservation = $reservationService->reserve($request->validated());

        return redirect()->route('reservations.complete', [
            'reservation_number' => $reservation->reservation_number,
        ]);
    }

    public function complete(Request $request)
    {
        $reservation = Reservation::query()
            ->where('reservation_number', $request->input('reservation_number'))
            ->firstOrFail();

        return view('reservations.complete', compact('reservation'));
    }
}
