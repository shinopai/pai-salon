<?php

namespace App\View\Components;

use App\Enums\StaffRole;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;

class Header extends Component
{
    public bool $isAuthenticated;

    public bool $isStaff;

    public bool $isAdmin;

    public ?string $staffName;

    public function __construct()
    {
        $user = Auth::user();
        $staff = $user?->staff;

        $this->isAuthenticated = $user !== null;
        $this->isAdmin = $staff?->role === StaffRole::ADMIN;
        $this->isStaff = $staff?->role === StaffRole::STAFF;
        $this->staffName = $staff?->name;
    }

    public function render(): View
    {
        return view('components.header');
    }
}
