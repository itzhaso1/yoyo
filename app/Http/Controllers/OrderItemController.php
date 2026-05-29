<?php

namespace App\Http\Controllers;

use App\Models\CafeSession;
use App\Models\MenuItem;
use App\Models\OperationLog;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function store(Request $request, CafeSession $cafeSession): RedirectResponse|JsonResponse
    {
        $this->abortIfClosed($cafeSession);

        $data = $request->validate([
            'menu_item_id' => ['nullable', 'exists:menu_items,id'],
            'item_name' => ['required_without:menu_item_id', 'nullable', 'string', 'max:120'],
            'price' => ['required_without:menu_item_id', 'nullable', 'numeric', 'min:0', 'max:999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        if (! empty($data['menu_item_id'])) {
            $menuItem = MenuItem::where('is_active', true)->findOrFail($data['menu_item_id']);
            $data['item_name'] = $menuItem->name;
            $data['price'] = $menuItem->price;
        }

        $orderItem = $cafeSession->orderItems()->create([
            'menu_item_id' => $data['menu_item_id'] ?? null,
            'item_name' => $data['item_name'],
            'price' => $data['price'],
            'quantity' => $data['quantity'],
        ]);

        $cafeSession->load('orderItems');
        $cafeSession->recalculateTotals();
        OperationLog::record('إضافة طلب', $orderItem, ['الفاتورة' => $cafeSession->invoice_number]);

        return $this->respond($request, 'تمت إضافة الطلب.');
    }

    public function update(Request $request, OrderItem $orderItem): RedirectResponse|JsonResponse
    {
        $session = $orderItem->cafeSession;
        $this->abortIfClosed($session);

        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        $orderItem->update($data);
        $session->load('orderItems');
        $session->recalculateTotals();
        OperationLog::record('تحديث طلب', $orderItem, $data);

        return $this->respond($request, 'تم تحديث الطلب.');
    }

    public function destroy(Request $request, OrderItem $orderItem): RedirectResponse|JsonResponse
    {
        $session = $orderItem->cafeSession;
        $this->abortIfClosed($session);

        OperationLog::record('حذف طلب', $orderItem, ['الصنف' => $orderItem->item_name]);
        $orderItem->delete();
        $session->load('orderItems');
        $session->recalculateTotals();

        return $this->respond($request, 'تم حذف الطلب.');
    }

    private function respond(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('status', $message);
    }

    private function abortIfClosed(CafeSession $session): void
    {
        abort_unless($session->isOpen(), 422, 'لا يمكن تعديل طلبات جلسة مغلقة أو ملغاة.');
    }
}
