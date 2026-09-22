<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;

class BusinessHourController extends Controller
{
    public function index()
    {
        $businessHours = BusinessHour::query()
            ->orderBy('day_of_week')
            ->get();

        return view('admin.business-hours.index', compact('businessHours'));
    }
}
