<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\OperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function index(): View
    {
        return view('menu-items.index', [
            'items' => MenuItem::orderBy('category')->orderBy('name')->get(),
            'categories' => ['shisha' => 'Shisha', 'food' => 'Food', 'drink' => 'Drink'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'category' => ['required', Rule::in(['shisha', 'food', 'drink'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $item = MenuItem::create($data + ['is_active' => $request->boolean('is_active', true)]);
        OperationLog::record('menu.created', $item, $data);

        return back()->with('status', 'تمت إضافة الصنف.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'category' => ['required', Rule::in(['shisha', 'food', 'drink'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $menuItem->update($data);
        OperationLog::record('menu.updated', $menuItem, $data);

        return back()->with('status', 'تم تحديث الصنف.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_active' => false]);
        OperationLog::record('menu.disabled', $menuItem);

        return back()->with('status', 'تم تعطيل الصنف مع الحفاظ على سجل الفواتير القديمة.');
    }
}
