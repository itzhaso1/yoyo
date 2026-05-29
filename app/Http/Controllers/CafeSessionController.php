<?php

namespace App\Http\Controllers;

use App\Models\CafeSession;
use App\Models\MenuItem;
use App\Models\OperationLog;
use App\Models\PosTable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CafeSessionController extends Controller
{
    public function open(PosTable $posTable): RedirectResponse
    {
        $session = DB::transaction(function () use ($posTable): CafeSession {
            $table = PosTable::whereKey($posTable->id)->lockForUpdate()->firstOrFail();
            $activeSession = $table->activeSession()->first();

            if ($activeSession) {
                return $activeSession;
            }

            $session = CafeSession::create([
                'user_id' => auth()->id(),
                'pos_table_id' => $table->id,
                'invoice_number' => $this->nextInvoiceNumber(),
                'status' => 'open',
                'opened_at' => now(),
            ]);

            $table->update(['status' => 'busy']);
            OperationLog::record('session.opened', $session, ['table' => $table->name]);

            return $session;
        });

        return redirect()->route('sessions.show', $session);
    }

    public function show(CafeSession $cafeSession): View
    {
        $cafeSession->load(['table', 'user', 'orderItems.menuItem']);
        $cafeSession->recalculateTotals();
        $menuItems = MenuItem::where('is_active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category');

        return view('sessions.show', [
            'session' => $cafeSession->fresh(['table', 'user', 'orderItems.menuItem']),
            'menuItems' => $menuItems,
            'categories' => ['shisha' => 'Shisha', 'food' => 'Food', 'drink' => 'Drink'],
        ]);
    }

    public function markBilling(CafeSession $cafeSession): RedirectResponse
    {
        $this->abortIfClosed($cafeSession);

        $cafeSession->table->update(['status' => 'billing']);
        OperationLog::record('session.billing', $cafeSession);

        return back()->with('status', 'تم تحويل الطاولة إلى مرحلة الحساب.');
    }

    public function close(Request $request, CafeSession $cafeSession): RedirectResponse
    {
        $this->abortIfClosed($cafeSession);

        $data = $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'tip' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ]);

        DB::transaction(function () use ($cafeSession, $data): void {
            $session = CafeSession::whereKey($cafeSession->id)->lockForUpdate()->firstOrFail();
            $session->load(['orderItems', 'table']);

            $session->discount = $data['discount'] ?? 0;
            $session->tip = $data['tip'] ?? 0;
            $session->recalculateTotals();
            $session->status = 'closed';
            $session->closed_at = now();
            $session->save();

            $session->table->update(['status' => 'free']);
            OperationLog::record('session.closed', $session, [
                'invoice_number' => $session->invoice_number,
                'total_price' => $session->total_price,
            ]);
        });

        return redirect()->route('sessions.invoice', $cafeSession)->with('status', 'تم إغلاق الجلسة وإرجاع الطاولة إلى free.');
    }

    public function invoice(CafeSession $cafeSession): View
    {
        $cafeSession->load(['table', 'user', 'orderItems']);

        return view('sessions.invoice', ['session' => $cafeSession]);
    }

    public function state(CafeSession $cafeSession): JsonResponse
    {
        $cafeSession->load(['table', 'orderItems']);
        $cafeSession->recalculateTotals();

        return response()->json([
            'session' => [
                'id' => $cafeSession->id,
                'invoice_number' => $cafeSession->invoice_number,
                'status' => $cafeSession->status,
                'table_status' => $cafeSession->table->status,
                'subtotal' => number_format((float) $cafeSession->subtotal, 2, '.', ''),
                'discount' => number_format((float) $cafeSession->discount, 2, '.', ''),
                'tip' => number_format((float) $cafeSession->tip, 2, '.', ''),
                'total_price' => number_format((float) $cafeSession->total_price, 2, '.', ''),
                'orders' => $cafeSession->orderItems->map(fn ($item): array => [
                    'id' => $item->id,
                    'item_name' => $item->item_name,
                    'price' => number_format((float) $item->price, 2, '.', ''),
                    'quantity' => $item->quantity,
                    'line_total' => number_format((float) $item->line_total, 2, '.', ''),
                ])->values(),
            ],
        ]);
    }

    private function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $latest = CafeSession::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('invoice_number');

        $next = $latest ? ((int) str($latest)->afterLast('-')->toString()) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function abortIfClosed(CafeSession $session): void
    {
        abort_if($session->isClosed(), 422, 'لا يمكن تعديل جلسة مغلقة.');
    }
}
