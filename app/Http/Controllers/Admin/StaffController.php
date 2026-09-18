<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\View\View;
use App\Http\Requests\StaffRequest;
use Illuminate\Http\RedirectResponse;

class StaffController extends Controller
{
    public function index(): View
    {
        $staffs = Staff::all();

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create(): View
    {
        return view('admin.staffs.create');
    }

    public function store(StaffRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Staff::forceCreate([
            'user_id' => $data['user_id'],
            'name' => $data['name'],
            'role' => $data['role'],
        ]);

        return redirect()->route('admin.staffs.index');
    }

    public function show(Staff $staff): View
    {
        return view('admin.staffs.show', compact('staff'));
    }
}
