<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StaffProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Menu;
use App\Models\Reservation;

class StaffController extends Controller
{
    public function dashboard(): View
    {
        $staff = Auth::user()->staff;

        $todayReservations = Reservation::with('menu')
            ->where('staff_id', $staff->id)
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        $nextReservation = $todayReservations
            ->first(fn($reservation) => $reservation->start_at->isFuture());

        return view('staff.dashboard', compact(
            'staff',
            'todayReservations',
            'nextReservation'
        ));
    }

    public function profile(): View
    {
        $staff = Auth::user()->staff;

        return view('staff.profile', compact('staff'));
    }

    public function profileEdit(): View
    {
        $staff = Auth::user()->staff;

        return view('staff.profile-edit', compact('staff'));
    }

    public function profileUpdate(
        StaffProfileUpdateRequest $request
    ): RedirectResponse {
        $staff = Auth::user()->staff;

        $staff->update(
            $request->validated()
        );

        return redirect()->route('staff.profile');
    }

    public function menus(): View
    {
        $staff = Auth::user()->staff;

        $menus = Menu::whereHas('staffs', function ($query) use ($staff) {
            $query->where('staffs.id', $staff->id);
        })->get();

        return view('staff.menus.index', compact('menus'));
    }
}
