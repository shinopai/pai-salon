<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StaffController extends Controller
{
    public function dashboard(): View
    {
        return view('staff.dashboard');
    }
}
