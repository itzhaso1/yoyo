<?php

namespace App\Http\Controllers;

use App\Models\CafeSession;
use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function daily(Request $request): View
    {
        $date = $request->date('date') ?: today();
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        $closedSessions = CafeSession::with(['table', 'user'])
            ->where('status', 'closed')
            ->whereBetween('closed_at', [$start, $end]);

        $summary = [
            'sales' => (clone $closedSessions)->sum('total_price'),
            'invoices' => (clone $closedSessions)->count(),
            'discounts' => (clone $closedSessions)->sum('discount'),
            'tips' => (clone $closedSessions)->sum('tip'),
        ];

        $topItems = DB::table('order_items')
            ->join('cafe_sessions', 'order_items.cafe_session_id', '=', 'cafe_sessions.id')
            ->select('order_items.item_name', DB::raw('SUM(order_items.quantity) as total_quantity'), DB::raw('SUM(order_items.quantity * order_items.price) as revenue'))
            ->where('cafe_sessions.status', 'closed')
            ->whereBetween('cafe_sessions.closed_at', [$start, $end])
            ->groupBy('order_items.item_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        $sessions = $closedSessions->latest('closed_at')->get();
        $logs = OperationLog::with('user')->latest()->limit(30)->get();

        return view('reports.daily', compact('date', 'summary', 'topItems', 'sessions', 'logs'));
    }
}
