<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessHour;
use App\Http\Requests\BusinessHourRequest;

class BusinessHourController extends Controller
{
    public function index()
    {
        $businessHours = BusinessHour::query()
            ->orderBy('day_of_week')
            ->get();

        $dayNames = [
            0 => '日曜日',
            1 => '月曜日',
            2 => '火曜日',
            3 => '水曜日',
            4 => '木曜日',
            5 => '金曜日',
            6 => '土曜日',
        ];

        return view('admin.business-hours.index', compact('businessHours', 'dayNames'));
    }

    public function update(BusinessHourRequest $request)
    {
        BusinessHour::query()
            ->where('day_of_week', $request->validated('day_of_week'))
            ->update([
                'open_time' => $request->validated('open_time'),
                'close_time' => $request->validated('close_time'),
            ]);

        return redirect()->route('admin.business-hours.index');
    }
}
