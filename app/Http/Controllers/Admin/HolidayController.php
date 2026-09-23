<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Http\Requests\HolidayRequest;

class HolidayController extends Controller
{
    public function index()
    {
        $holidays = Holiday::all();

        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(HolidayRequest $request)
    {
        Holiday::create($request->validated());

        return redirect()->route('admin.holidays.index');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(HolidayRequest $request, Holiday $holiday)
    {
        $holiday->update($request->validated());

        return redirect()->route('admin.holidays.index');
    }
}
