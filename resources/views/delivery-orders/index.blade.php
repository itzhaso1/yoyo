@extends('layouts.app')

@section('title', 'طلبات خارجية - بيت جدي')

@section('content')
<section class="panel">
    <h1>طلبات خارجية / توصيل</h1>
    <p class="muted">استخدمها لأي طلب خارج الطاولات: اكتب الصنف، الموقع، السعر والكمية.</p>
    <form method="POST" action="{{ route('delivery-orders.store') }}" class="grid grid-4">
        @csrf
        <input class="field" name="customer_name" placeholder="اسم الزبون اختياري">
        <input class="field" name="phone" placeholder="رقم الهاتف اختياري">
        <input class="field" name="location" placeholder="الموقع / العنوان" required>
        <input class="field" name="item_name" placeholder="الطلب / الصنف" required>
        <input class="field" type="number" name="quantity" min="1" value="1" placeholder="الكمية" required>
        <input class="field" type="number" name="price" step="0.01" min="0" placeholder="السعر" required>
        <input class="field" name="notes" placeholder="ملاحظات اختيارية">
        <button class="btn btn-primary" type="submit">إضافة طلب خارجي</button>
    </form>
</section>

<section class="panel" style="margin-top:18px;">
    <h2>آخر الطلبات الخارجية</h2>
    <table class="data-table">
        <thead>
            <tr><th>الوقت</th><th>الطلب</th><th>الموقع</th><th>الكمية</th><th>الإجمالي</th><th>الحالة</th><th>تحكم</th></tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                <tr>
                    <td>{{ $order->ordered_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $order->item_name }}<br><small class="muted">{{ $order->customer_name }} {{ $order->phone ? '- '.$order->phone : '' }}</small></td>
                    <td>{{ $order->location }}<br><small class="muted">{{ $order->notes }}</small></td>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ number_format($order->total_price, 2) }}</td>
                    <td>{{ $statuses[$order->status] ?? $order->status }}</td>
                    <td>
                        <form method="POST" action="{{ route('delivery-orders.update', $order) }}" style="display:flex;gap:6px;margin-bottom:6px;">
                            @csrf
                            @method('PATCH')
                            <select name="status" aria-label="حالة الطلب">
                                @foreach($statuses as $status => $label)
                                    <option value="{{ $status }}" @selected($order->status === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-small" type="submit">حفظ</button>
                        </form>
                        <form method="POST" action="{{ route('delivery-orders.destroy', $order) }}" onsubmit="return confirm('حذف الطلب الخارجي؟');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-small" type="submit">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">لا توجد طلبات خارجية بعد.</td></tr>
            @endforelse
        </tbody>
    </table>
</section>
@endsection
