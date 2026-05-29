@extends('layouts.app')

@section('title', 'الطاولات - Cafe POS')

@section('content')
<div class="grid grid-2">
    <section class="panel">
        <h1>لوحة الطاولات</h1>
        <p class="muted">اضغط على أي طاولة لفتح جلسة أو متابعة الجلسة المفتوحة.</p>
        <div class="legend">
            <span><i class="dot" style="background:#22c55e"></i> Free</span>
            <span><i class="dot" style="background:#ef4444"></i> Busy</span>
            <span><i class="dot" style="background:#f59e0b"></i> Reserved</span>
            <span><i class="dot" style="background:#3b82f6"></i> Billing</span>
        </div>
    </section>

    @if(auth()->user()->isAdmin())
        <section class="panel">
            <h2>إضافة طاولة</h2>
            <form method="POST" action="{{ route('tables.store') }}" class="grid grid-4">
                @csrf
                <input class="field" name="name" placeholder="اسم الطاولة" required>
                <select name="section">
                    @foreach($sections as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input class="field" type="number" name="seats" placeholder="عدد الكراسي" min="1">
                <button class="btn btn-primary" type="submit">إضافة</button>
            </form>
        </section>
    @endif
</div>

@foreach($sections as $sectionKey => $sectionLabel)
    <section class="panel" style="margin-top:18px;">
        <h2>{{ $sectionLabel }}</h2>
        <div class="table-grid">
            @forelse(($tablesBySection[$sectionKey] ?? collect()) as $table)
                <div>
                    <form method="POST" action="{{ route('tables.open', $table) }}">
                        @csrf
                        <button class="table-card status-{{ $table->status }}" data-table-card="{{ $table->id }}" type="submit">
                            <strong style="font-size:26px;display:block;">{{ $table->name }}</strong>
                            <span class="badge" data-table-status="{{ $table->id }}">{{ strtoupper($table->status) }}</span>
                            <div style="margin-top:14px;" class="muted">{{ $table->seats ? $table->seats.' seats' : 'No seats set' }}</div>
                            @if($table->activeSession)
                                <div style="margin-top:8px;">{{ $table->activeSession->invoice_number }}</div>
                            @endif
                        </button>
                    </form>
                    @if(auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('tables.update', $table) }}" style="display:flex;gap:6px;margin-top:8px;">
                            @csrf
                            @method('PATCH')
                            <select name="status" style="padding:7px;border-radius:9px;">
                                @foreach($statuses as $status => $label)
                                    <option value="{{ $status }}" @selected($table->status === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-small" type="submit">حفظ</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="muted">لا توجد طاولات في هذا القسم.</p>
            @endforelse
        </div>
    </section>
@endforeach
@endsection

@push('scripts')
<script>
const statusClasses = ['status-free', 'status-busy', 'status-reserved', 'status-billing'];
async function refreshTables() {
    try {
        const response = await fetch('{{ route('tables.state') }}', {headers: {'Accept': 'application/json'}});
        const payload = await response.json();
        payload.tables.forEach((table) => {
            const card = document.querySelector(`[data-table-card="${table.id}"]`);
            const label = document.querySelector(`[data-table-status="${table.id}"]`);
            if (!card || !label) return;
            card.classList.remove(...statusClasses);
            card.classList.add(`status-${table.status}`);
            label.textContent = table.status.toUpperCase();
        });
    } catch (error) {
        console.warn('table refresh failed', error);
    }
}
setInterval(refreshTables, 5000);
</script>
@endpush
