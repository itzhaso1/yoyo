@extends('layouts.app')

@section('title', 'فاتورة '.$session->invoice_number)

@section('content')
<div class="no-print" style="text-align:center;margin-bottom:16px;">
    <a class="btn" href="{{ route('sessions.show', $session) }}">رجوع للجلسة</a>
    <button class="btn btn-primary" onclick="window.print()">طباعة</button>
</div>

<section class="invoice">
    <h2 style="text-align:center;margin:0;">نظام كاشير المقهى</h2>
    <p style="text-align:center;margin:6px 0 18px;">فاتورة ضريبية مبسطة</p>
    <div style="display:grid;gap:5px;font-size:14px;">
        <span>رقم الفاتورة: <strong>{{ $session->invoice_number }}</strong></span>
        <span>الطاولة: {{ $session->table->name }}</span>
        <span>الموظف: {{ $session->user?->name ?? '-' }}</span>
        <span>وقت الفتح: {{ $session->opened_at?->format('Y-m-d H:i') }}</span>
        <span>وقت الإغلاق: {{ $session->closed_at?->format('Y-m-d H:i') ?? '-' }}</span>
    </div>
    <table style="margin-top:18px;">
        <thead>
            <tr><th>الصنف</th><th>الكمية</th><th>الإجمالي</th></tr>
        </thead>
        <tbody>
            @foreach($session->orderItems as $order)
                <tr>
                    <td>{{ $order->item_name }}<br><small>{{ number_format($order->price, 2) }}</small></td>
                    <td>{{ $order->quantity }}</td>
                    <td>{{ number_format($order->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:18px;display:grid;gap:7px;">
        <span>المجموع قبل الخصم: {{ number_format($session->subtotal, 2) }}</span>
        <span>الخصم: {{ number_format($session->discount, 2) }}</span>
        <span>الإكرامية: {{ number_format($session->tip, 2) }}</span>
        <strong style="font-size:22px;">الإجمالي النهائي: {{ number_format($session->total_price, 2) }}</strong>
    </div>
    <p style="text-align:center;margin-top:22px;">شكراً لزيارتكم</p>
</section>
@endsection
