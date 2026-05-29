@extends('layouts.app')

@section('title', 'التقارير اليومية')

@section('content')
<section class="panel">
    <h1>التقرير اليومي</h1>
    <form method="GET" action="{{ route('reports.daily') }}" style="display:flex;gap:10px;flex-wrap:wrap;">
        <input class="field" style="max-width:220px;" type="date" name="date" value="{{ $date->format('Y-m-d') }}">
        <button class="btn btn-primary" type="submit">عرض</button>
        <button class="btn" type="button" onclick="window.print()">طباعة التقرير</button>
    </form>
</section>

<div class="stats" style="margin-top:18px;">
    <div class="stat"><span>إجمالي المبيعات</span><strong>{{ number_format($summary['sales'], 2) }}</strong></div>
    <div class="stat"><span>عدد الفواتير</span><strong>{{ $summary['invoices'] }}</strong></div>
    <div class="stat"><span>الخصومات</span><strong>{{ number_format($summary['discounts'], 2) }}</strong></div>
    <div class="stat"><span>Tips</span><strong>{{ number_format($summary['tips'], 2) }}</strong></div>
</div>

<div class="grid grid-2" style="margin-top:18px;align-items:start;">
    <section class="panel">
        <h2>أكثر الأصناف مبيعاً</h2>
        <table class="data-table">
            <thead><tr><th>الصنف</th><th>الكمية</th><th>المبيعات</th></tr></thead>
            <tbody>
                @forelse($topItems as $item)
                    <tr><td>{{ $item->item_name }}</td><td>{{ $item->total_quantity }}</td><td>{{ number_format($item->revenue, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="muted">لا توجد مبيعات لهذا اليوم.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>

    <section class="panel">
        <h2>آخر العمليات</h2>
        <table class="data-table">
            <thead><tr><th>الوقت</th><th>المستخدم</th><th>العملية</th></tr></thead>
            <tbody>
                @foreach($logs as $log)
                    <tr><td>{{ $log->created_at->format('H:i') }}</td><td>{{ $log->user?->username ?? '-' }}</td><td>{{ $log->action }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </section>
</div>

<section class="panel" style="margin-top:18px;">
    <h2>الفواتير المغلقة</h2>
    <table class="data-table">
        <thead><tr><th>الفاتورة</th><th>الطاولة</th><th>الموظف</th><th>الإغلاق</th><th>الإجمالي</th></tr></thead>
        <tbody>
            @foreach($sessions as $session)
                <tr>
                    <td><a href="{{ route('sessions.invoice', $session) }}">{{ $session->invoice_number }}</a></td>
                    <td>{{ $session->table->name }}</td>
                    <td>{{ $session->user?->name ?? '-' }}</td>
                    <td>{{ $session->closed_at?->format('H:i') }}</td>
                    <td>{{ number_format($session->total_price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endsection
