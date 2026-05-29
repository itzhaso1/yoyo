@extends('layouts.app')

@section('title', 'الطاولات - بيت جدي')

@section('content')
<section class="panel">
    <h1>لوحة الطاولات - بيت جدي</h1>
    <p class="muted">اضغط على أي طاولة لفتح جلسة جديدة أو متابعة الجلسة المفتوحة.</p>
    @if(auth()->user()->isAdmin())
        <div class="alert alert-ok" style="margin:12px 0;">أنت داخل بحساب أدمن: أدوات تعديل وحذف الطاولات مفعلة أسفل كل طاولة. إضافة طاولة وتغيير كلمة المرور انتقلت إلى صفحة الإعدادات.</div>
    @else
        <div class="alert alert-error" style="margin:12px 0;">أنت داخل بحساب كاشير: تعديل وحذف الطاولات يظهر فقط لحساب الأدمن.</div>
    @endif
    <div class="legend">
        <span><i class="dot" style="background:#22c55e"></i> متاحة</span>
        <span><i class="dot" style="background:#ef4444"></i> مشغولة</span>
        <span><i class="dot" style="background:#f59e0b"></i> محجوزة</span>
        <span><i class="dot" style="background:#3b82f6"></i> قيد الحساب</span>
    </div>
</section>

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
                            <span class="badge" data-table-status="{{ $table->id }}">{{ $statuses[$table->status] ?? $table->status }}</span>
                            <div style="margin-top:14px;" class="muted">{{ $table->seats ? $table->seats.' كرسي' : 'لم يحدد عدد الكراسي' }}</div>
                            @if($table->activeSession)
                                <div style="margin-top:8px;">{{ $table->activeSession->invoice_number }}</div>
                            @endif
                            <div style="margin-top:6px;" data-table-timer="{{ $table->id }}" data-opened-at="{{ $table->activeSession?->opened_at?->toIso8601String() }}">{{ $table->activeSession ? '00:00:00' : '' }}</div>
                        </button>
                    </form>
                    @if(auth()->user()->isAdmin())
                        <div style="margin-top:10px;padding:10px;border:1px solid var(--line);border-radius:14px;background:#101827;">
                            <form method="POST" action="{{ route('tables.update', $table) }}" class="grid" style="gap:8px;">
                                @csrf
                                @method('PATCH')
                                <input class="field" name="name" value="{{ $table->name }}" placeholder="اسم الطاولة" required>
                                <select name="section" aria-label="القسم">
                                    @foreach($sections as $key => $label)
                                        <option value="{{ $key }}" @selected($table->section === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input class="field" type="number" name="seats" value="{{ $table->seats }}" placeholder="عدد الكراسي" min="1">
                                <select name="status" aria-label="حالة الطاولة">
                                    @foreach($statuses as $status => $label)
                                        <option value="{{ $status }}" @selected($table->status === $status)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input class="field" type="number" name="sort_order" value="{{ $table->sort_order }}" placeholder="ترتيب العرض" min="0">
                                <button class="btn btn-small" type="submit">حفظ تعديل الطاولة</button>
                            </form>
                            <form method="POST" action="{{ route('tables.destroy', $table) }}" style="margin-top:8px;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الطاولة؟');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-small" type="submit" @disabled((bool) $table->activeSession)>حذف الطاولة</button>
                                @if($table->activeSession)
                                    <small class="muted" style="display:block;margin-top:6px;">لا يمكن حذف طاولة عليها جلسة مفتوحة.</small>
                                @endif
                            </form>
                        </div>
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
const statusLabels = @json($statuses);
function formatDuration(totalSeconds) {
    const seconds = Math.max(0, Math.floor(totalSeconds));
    const hours = String(Math.floor(seconds / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((seconds % 3600) / 60)).padStart(2, '0');
    const remainingSeconds = String(seconds % 60).padStart(2, '0');
    return `${hours}:${minutes}:${remainingSeconds}`;
}
function refreshTableTimers() {
    document.querySelectorAll('[data-table-timer]').forEach((timer) => {
        const openedAt = timer.dataset.openedAt;
        if (!openedAt) {
            timer.textContent = '';
            return;
        }
        timer.textContent = formatDuration((Date.now() - new Date(openedAt).getTime()) / 1000);
    });
}
async function refreshTables() {
    try {
        const response = await fetch('{{ route('tables.state') }}', {headers: {'Accept': 'application/json'}});
        const payload = await response.json();
        payload.tables.forEach((table) => {
            const card = document.querySelector(`[data-table-card="${table.id}"]`);
            const label = document.querySelector(`[data-table-status="${table.id}"]`);
            if (!card || !label) return;
            const timer = document.querySelector(`[data-table-timer="${table.id}"]`);
            if (timer) {
                timer.dataset.openedAt = table.active_session_opened_at || '';
                if (!table.active_session_opened_at) timer.textContent = '';
            }
            card.classList.remove(...statusClasses);
            card.classList.add(`status-${table.status}`);
            label.textContent = statusLabels[table.status] || table.status;
        });
    } catch (error) {
        console.warn('تعذر تحديث الطاولات', error);
    }
}
refreshTableTimers();
setInterval(refreshTableTimers, 1000);
setInterval(refreshTables, 5000);
</script>
@endpush
