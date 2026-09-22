<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Support\Facades\DB;

class StaffMenuService
{
    public function syncMenus(Staff $staff, array $menuIds): void
    {
        DB::transaction(function () use ($staff, $menuIds) {
            $staff->menus()->sync($menuIds);
        });
    }
}
