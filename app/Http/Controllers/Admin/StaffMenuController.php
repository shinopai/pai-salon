<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffMenuUpdateRequest;
use App\Models\Menu;
use App\Models\Staff;
use App\Services\StaffMenuService;

class StaffMenuController extends Controller
{
    public function index()
    {
        $staffs = Staff::with('menus')->get();
        $menus = Menu::query()->get();

        return view('admin.staff-menus.index', compact('staffs', 'menus'));
    }

    public function update(
        StaffMenuUpdateRequest $request,
        StaffMenuService $staffMenuService
    ) {
        $staff = Staff::findOrFail($request->validated('staff_id'));
        $menuIds = $request->validated('menu_ids', []);

        $staffMenuService->syncMenus($staff, $menuIds);

        return redirect()->route('admin.staff-menus.index');
    }
}
