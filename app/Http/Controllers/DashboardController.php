<?php

namespace App\Http\Controllers;

use App\Models\PosTable;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tablesBySection = PosTable::with('activeSession')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('section');

        return view('dashboard.index', [
            'tablesBySection' => $tablesBySection,
            'sections' => ['indoor' => 'داخلي', 'outdoor' => 'خارجي', 'vip' => 'كبار الشخصيات'],
            'statuses' => ['free' => 'متاحة', 'busy' => 'مشغولة', 'reserved' => 'محجوزة', 'billing' => 'قيد الحساب'],
        ]);
    }

    public function state(): JsonResponse
    {
        $tables = PosTable::with('activeSession')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PosTable $table): array => [
                'id' => $table->id,
                'name' => $table->name,
                'section' => $table->section,
                'status' => $table->status,
                'active_session_id' => $table->activeSession?->id,
                'active_session_opened_at' => $table->activeSession?->opened_at?->toIso8601String(),
                'active_session_url' => $table->activeSession ? route('sessions.show', $table->activeSession) : null,
                'open_url' => route('tables.open', $table),
            ]);

        return response()->json(['tables' => $tables]);
    }
}
