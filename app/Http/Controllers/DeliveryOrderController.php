<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\OperationLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DeliveryOrderController extends Controller
{
    public function index(): View
    {
        return view('delivery-orders.index', [
            'orders' => DeliveryOrder::with('user')->latest('ordered_at')->limit(100)->get(),
            'statuses' => ['pending' => 'قيد التجهيز', 'completed' => 'مكتمل', 'cancelled' => 'ملغى'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:50'],
            'location' => ['required', 'string', 'max:255'],
            'item_name' => ['required', 'string', 'max:160'],
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DeliveryOrder::create($data + [
            'user_id' => auth()->id(),
            'total_price' => (float) $data['price'] * (int) $data['quantity'],
            'status' => 'pending',
            'ordered_at' => now(),
        ]);

        OperationLog::record('إضافة طلب خارجي', $order, ['الصنف' => $order->item_name, 'الموقع' => $order->location]);

        return back()->with('status', 'تمت إضافة طلب التوصيل/الخارجي.');
    }

    public function update(Request $request, DeliveryOrder $deliveryOrder): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
        ]);

        $deliveryOrder->update($data);
        OperationLog::record('تحديث طلب خارجي', $deliveryOrder, $data);

        return back()->with('status', 'تم تحديث حالة الطلب الخارجي.');
    }

    public function destroy(DeliveryOrder $deliveryOrder): RedirectResponse
    {
        OperationLog::record('حذف طلب خارجي', $deliveryOrder, ['الصنف' => $deliveryOrder->item_name]);
        $deliveryOrder->delete();

        return back()->with('status', 'تم حذف الطلب الخارجي.');
    }
}
