<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;

class StaffMenuController extends Controller
{
    public function index()
    {
        $staffs = Staff::with('menus')->get();

        return view('admin.staff-menus.index', compact('staffs'));
    }
}
