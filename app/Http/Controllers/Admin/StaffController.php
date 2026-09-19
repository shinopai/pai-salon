<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\View\View;
use App\Http\Requests\StaffRequest;
use App\Http\Requests\AdminStaffUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

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

    public function edit(Staff $staff): View
    {
        return view('admin.staffs.edit', compact('staff'));
    }

    public function update(
        AdminStaffUpdateRequest $request,
        Staff $staff
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($data, $staff) {
            $staff->forceFill([
                'name' => $data['name'],
                'role' => $data['role'],
            ])->save();

            $staff->user->update([
                'email' => $data['email'],
            ]);
        });

        return redirect()->route('admin.staffs.show', $staff);
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $staff->delete();

        return redirect()->route('admin.staffs.index');
    }
}
