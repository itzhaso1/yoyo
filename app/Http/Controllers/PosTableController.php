<?php

namespace App\Http\Controllers;

use App\Models\OperationLog;
use App\Models\PosTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PosTableController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:pos_tables,name'],
            'section' => ['required', Rule::in(['indoor', 'outdoor', 'vip'])],
            'status' => ['nullable', Rule::in(['free', 'busy', 'reserved', 'billing'])],
            'seats' => ['nullable', 'integer', 'min:1', 'max:99'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $table = PosTable::create($data + ['status' => 'free', 'sort_order' => 0]);
        OperationLog::record('table.created', $table, $data);

        return back()->with('status', 'تمت إضافة الطاولة.');
    }

    public function update(Request $request, PosTable $posTable): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('pos_tables', 'name')->ignore($posTable)],
            'section' => ['sometimes', 'required', Rule::in(['indoor', 'outdoor', 'vip'])],
            'status' => ['sometimes', 'required', Rule::in(['free', 'busy', 'reserved', 'billing'])],
            'seats' => ['nullable', 'integer', 'min:1', 'max:99'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        if (($data['status'] ?? null) === 'free' && $posTable->activeSession()->exists()) {
            return back()->withErrors(['status' => 'لا يمكن جعل الطاولة free قبل إغلاق الجلسة الحالية.']);
        }

        $posTable->update($data);
        OperationLog::record('table.updated', $posTable, $data);

        return back()->with('status', 'تم تحديث الطاولة.');
    }
}
