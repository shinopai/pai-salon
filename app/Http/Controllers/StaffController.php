<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StaffProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Menu;

class StaffController extends Controller
{
    public function dashboard(): View
    {
        return view('staff.dashboard');
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
