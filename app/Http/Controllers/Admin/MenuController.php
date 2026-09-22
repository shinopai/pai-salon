<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MenuRequest;
use App\Models\Menu;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()->get();

        return view('admin.menus.index', compact('menus'));
    }

    public function create()
    {
        return view('admin.menus.create');
    }

    public function store(MenuRequest $request)
    {
        Menu::create($request->validated());

        return redirect()->route('admin.menus.index');
    }

    public function show(Menu $menu)
    {
        return view('admin.menus.show', compact('menu'));
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', compact('menu'));
    }

    public function update(MenuRequest $request, Menu $menu)
    {
        $menu->update($request->validated());

        return redirect()->route('admin.menus.show', $menu);
    }
}
