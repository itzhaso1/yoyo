@extends('layouts.app')

@section('title', 'جلسة '.$session->invoice_number)

@section('content')
<div class="grid grid-2">
    <section class="panel">
        <h1>جلسة {{ $session->invoice_number }}</h1>
        <p class="muted">الطاولة: {{ $session->tableName() }} | الموظف: {{ $session->user?->name ?? 'غير محدد' }} | الحالة: <span id="table-status">{{ $statuses[$session->tableStatus()] ?? $session->tableStatus() }}</span></p>
        <div class="stats" style="grid-template-columns:repeat(3,minmax(0,1fr));">
            <div class="stat"><span>المجموع قبل الخصم</span><strong id="subtotal">{{ number_format($session->subtotal, 2) }}</strong></div>
            <div class="stat"><span>الخصم</span><strong id="discount">{{ number_format($session->discount, 2) }}</strong></div>
            <div class="stat"><span>الإجمالي النهائي</span><strong id="total-price">{{ number_format($session->total_price, 2) }}</strong></div>
        </div>
    </section>

    <section class="panel">
        <h2>الحساب والإغلاق</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
            <form method="POST" action="{{ route('sessions.billing', $session) }}">
                @csrf
                @method('PATCH')
                <button class="btn" @disabled(! $session->isOpen())>تحويل إلى قيد الحساب</button>
            </form>
            <form method="POST" action="{{ route('sessions.cancel', $session) }}" onsubmit="return confirm('هل تريد إلغاء الجلسة؟ لن تدخل في المبيعات وسيتم تحرير الطاولة.');">
                @csrf
                <button class="btn btn-danger" type="submit" @disabled(! $session->isOpen())>إلغاء الجلسة / كنسل الطاولة</button>
            </form>
            <a class="btn" href="{{ route('sessions.invoice', $session) }}">طباعة الفاتورة</a>
        </div>
        <form method="POST" action="{{ route('sessions.close', $session) }}" class="grid grid-3">
            @csrf
            <input class="field" type="number" step="0.01" min="0" name="discount" value="{{ old('discount', $session->discount) }}" placeholder="الخصم">
            <input class="field" type="number" step="0.01" min="0" name="tip" value="{{ old('tip', $session->tip) }}" placeholder="إكرامية اختيارية">
            <button class="btn btn-primary" type="submit" @disabled(! $session->isOpen())>إغلاق الجلسة</button>
        </form>
    </section>
</div>

<div class="grid grid-2" style="margin-top:18px;align-items:start;">
    <section class="panel">
        <h2>المنيو</h2>
        <p class="muted">الأصناف مقسمة حسب النوع حتى تختار بسرعة بدون بحث طويل.</p>
        @foreach($menuGroups as $group)
            <section style="margin-top:16px;padding:14px;border:1px solid var(--line);border-radius:16px;background:#0f172a;">
                <h3 style="margin-top:0;">{{ $group['label'] }}</h3>
                <div class="grid grid-3">
                    @foreach($group['items'] as $item)
                        <form method="POST" action="{{ route('orders.store', $session) }}">
                            @csrf
                            <input type="hidden" name="menu_item_id" value="{{ $item->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button class="menu-button" type="submit" @disabled(! $session->isOpen())>
                                <strong>{{ $item->name }}</strong>
                                <span class="muted" style="display:block;margin-top:6px;">{{ number_format($item->price, 2) }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endforeach
        <hr style="border-color:var(--line);margin:20px 0;">
        <h3>طلب مخصص</h3>
        <form method="POST" action="{{ route('orders.store', $session) }}" class="grid grid-4">
            @csrf
            <input class="field" name="item_name" placeholder="اسم الصنف" @disabled(! $session->isOpen())>
            <input class="field" type="number" step="0.01" min="0" name="price" placeholder="السعر" @disabled(! $session->isOpen())>
            <input class="field" type="number" min="1" name="quantity" value="1" placeholder="الكمية" @disabled(! $session->isOpen())>
            <button class="btn btn-primary" type="submit" @disabled(! $session->isOpen())>إضافة الطلب</button>
        </form>
    </section>

    <section class="panel">
        <h2>الطلبات</h2>
        <div id="orders-list" data-count="{{ $session->orderItems->count() }}">
            @forelse($session->orderItems as $order)
                <div class="order-card">
                    <form id="update-order-{{ $order->id }}" method="POST" action="{{ route('orders.update', $order) }}" style="display:contents;">
                        @csrf
                        @method('PATCH')
                        <input class="field" name="item_name" value="{{ $order->item_name }}" @disabled(! $session->isOpen())>
                        <input class="field" type="number" step="0.01" min="0" name="price" value="{{ $order->price }}" @disabled(! $session->isOpen())>
                        <input class="field" type="number" min="1" name="quantity" value="{{ $order->quantity }}" @disabled(! $session->isOpen())>
                        <strong>{{ number_format($order->line_total, 2) }}</strong>
                        <button class="btn btn-small" type="submit" @disabled(! $session->isOpen())>تحديث</button>
                    </form>
                    <form method="POST" action="{{ route('orders.destroy', $order) }}" style="grid-column:1 / -1;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-small" type="submit" @disabled(! $session->isOpen())>حذف</button>
                    </form>
                </div>
            @empty
                <p class="muted">لا توجد طلبات بعد.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
const sessionClosed = @json(! $session->isOpen());
const statusLabels = @json($statuses);
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const ordersBase = '{{ url('/orders') }}';
function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[char]));
}
function orderCard(order) {
    const disabled = sessionClosed ? 'disabled' : '';
    const safeName = escapeHtml(order.item_name);
    return `
        <div class="order-card">
            <form method="POST" action="${ordersBase}/${order.id}" style="display:contents;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="PATCH">
                <input class="field" name="item_name" value="${safeName}" ${disabled}>
                <input class="field" type="number" step="0.01" min="0" name="price" value="${order.price}" ${disabled}>
                <input class="field" type="number" min="1" name="quantity" value="${order.quantity}" ${disabled}>
                <strong>${order.line_total}</strong>
                <button class="btn btn-small" type="submit" ${disabled}>تحديث</button>
            </form>
            <form method="POST" action="${ordersBase}/${order.id}" style="grid-column:1 / -1;">
                <input type="hidden" name="_token" value="${csrf}">
                <input type="hidden" name="_method" value="DELETE">
                <button class="btn btn-danger btn-small" type="submit" ${disabled}>حذف</button>
            </form>
        </div>`;
}
async function refreshSession() {
    if (document.activeElement && ['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
    try {
        const response = await fetch('{{ route('sessions.state', $session) }}', {headers: {'Accept': 'application/json'}});
        const payload = await response.json();
        document.getElementById('subtotal').textContent = payload.session.subtotal;
        document.getElementById('discount').textContent = payload.session.discount;
        document.getElementById('total-price').textContent = payload.session.total_price;
        document.getElementById('table-status').textContent = statusLabels[payload.session.table_status] || payload.session.table_status;
        document.getElementById('orders-list').innerHTML = payload.session.orders.map(orderCard).join('') || '<p class="muted">لا توجد طلبات بعد.</p>';
    } catch (error) {
        console.warn('تعذر تحديث الجلسة', error);
    }
}
setInterval(refreshSession, 5000);
</script>
@endpush
