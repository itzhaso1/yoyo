<?php

namespace App\Http\Controllers;

use App\Models\CafeSession;
use App\Models\OperationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        $soldItems = $this->soldItemsBetween($start, $end);
        $topItems = $soldItems->take(10);
        $typeTotals = $this->typeTotals($soldItems);
        $shishaItems = $soldItems
            ->filter(fn ($item): bool => $this->reportGroupKey($item->item_name, $item->category) === 'shisha')
            ->values();
        $shishaSummary = [
            'quantity' => $shishaItems->sum('total_quantity'),
            'revenue' => $shishaItems->sum('revenue'),
        ];

        $sessions = $closedSessions->latest('closed_at')->get();
        $logs = OperationLog::with('user')->latest()->limit(30)->get();

        return view('reports.daily', compact('date', 'summary', 'topItems', 'typeTotals', 'shishaItems', 'shishaSummary', 'sessions', 'logs'));
    }

    private function soldItemsBetween($start, $end): Collection
    {
        return DB::table('order_items')
            ->join('cafe_sessions', 'order_items.cafe_session_id', '=', 'cafe_sessions.id')
            ->leftJoin('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->select(
                'order_items.item_name',
                DB::raw('COALESCE(menu_items.category, order_items.item_name) as category'),
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.quantity * order_items.price) as revenue')
            )
            ->where('cafe_sessions.status', 'closed')
            ->whereBetween('cafe_sessions.closed_at', [$start, $end])
            ->groupBy('order_items.item_name', 'menu_items.category')
            ->orderByDesc('total_quantity')
            ->get()
            ->map(function ($item) {
                $item->total_quantity = (int) $item->total_quantity;
                $item->revenue = (float) $item->revenue;

                return $item;
            });
    }

    private function typeTotals(Collection $soldItems): Collection
    {
        return $soldItems
            ->groupBy(fn ($item): string => $this->reportGroupKey($item->item_name, $item->category))
            ->map(fn (Collection $items, string $key): array => [
                'label' => $this->reportGroupLabel($key),
                'quantity' => $items->sum('total_quantity'),
                'revenue' => $items->sum('revenue'),
            ])
            ->sortBy(fn (array $group): int => $this->reportGroupSort($group['label']))
            ->values();
    }

    private function reportGroupKey(string $itemName, ?string $category): string
    {
        if ($category === 'shisha') {
            return 'shisha';
        }

        if ($category === 'food') {
            return 'food';
        }

        if (str_starts_with($itemName, 'كوكتيل -')) {
            return 'cocktail';
        }

        if (str_starts_with($itemName, 'سموذي -')) {
            return 'smoothie';
        }

        if (str_starts_with($itemName, 'قهوة باردة -') || str_contains($itemName, 'قهوة') || str_contains($itemName, 'لاتيه') || str_contains($itemName, 'موكا') || str_contains($itemName, 'كابتشينو')) {
            return 'coffee';
        }

        if (str_starts_with($itemName, 'موهيتو طاقة -')) {
            return 'mojito';
        }

        if (str_starts_with($itemName, 'ملك شيك -')) {
            return 'king_shake';
        }

        if (str_starts_with($itemName, 'شيك -')) {
            return 'shake';
        }

        return 'other_drinks';
    }

    private function reportGroupLabel(string $key): string
    {
        return [
            'cocktail' => 'كوكتيل',
            'smoothie' => 'سموذي',
            'coffee' => 'قهوة',
            'mojito' => 'موهيتو طاقة',
            'king_shake' => 'ملك شيك',
            'shake' => 'شيك',
            'shisha' => 'شيشة',
            'food' => 'أكل',
            'other_drinks' => 'مشروبات أخرى',
        ][$key] ?? 'مشروبات أخرى';
    }

    private function reportGroupSort(string $label): int
    {
        $position = array_search($label, $this->reportGroupOrder(), true);

        return $position === false ? 999 : $position;
    }

    private function reportGroupOrder(): array
    {
        return ['كوكتيل', 'سموذي', 'قهوة', 'موهيتو طاقة', 'ملك شيك', 'شيك', 'شيشة', 'أكل', 'مشروبات أخرى'];
    }
}
