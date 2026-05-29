@extends('layouts.app')

@section('title', 'الطاولات - نظام كاشير المقهى')

@section('content')
<div class="grid grid-2">
    <section class="panel">
        <h1>لوحة الطاولات</h1>
        <p class="muted">اضغط على أي طاولة لفتح جلسة جديدة أو متابعة الجلسة المفتوحة.</p>
        <div class="legend">
            <span><i class="dot" style="background:#22c55e"></i> متاحة</span>
            <span><i class="dot" style="background:#ef4444"></i> مشغولة</span>
            <span><i class="dot" style="background:#f59e0b"></i> محجوزة</span>
            <span><i class="dot" style="background:#3b82f6"></i> قيد الحساب</span>
        </div>
    </section>

    <section class="panel">
        <h2>تغيير كلمة المرور</h2>
        <p class="muted">غيّر كلمة مرور حسابك من هنا. لن يتم حذف الحساب عند تسجيل الخروج.</p>
        <form method="POST" action="{{ route('profile.password.update') }}" class="grid grid-3">
            @csrf
            @method('PATCH')
            <input class="field" type="password" name="current_password" placeholder="كلمة المرور الحالية" required autocomplete="current-password">
            <input class="field" type="password" name="password" placeholder="كلمة المرور الجديدة" required autocomplete="new-password">
            <input class="field" type="password" name="password_confirmation" placeholder="تأكيد كلمة المرور الجديدة" required autocomplete="new-password">
            <button class="btn btn-primary" type="submit">حفظ كلمة المرور</button>
        </form>
    </section>

    @if(auth()->user()->isAdmin())
        <section class="panel">
            <h2>إضافة طاولة من لوحة الأدمن</h2>
            <form method="POST" action="{{ route('tables.store') }}" class="grid grid-4">
                @csrf
                <input class="field" name="name" placeholder="اسم الطاولة" required>
                <select name="section" aria-label="القسم">
                    @foreach($sections as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input class="field" type="number" name="seats" placeholder="عدد الكراسي" min="1">
                <button class="btn btn-primary" type="submit">إضافة طاولة</button>
            </form>
        </section>

        <section class="panel">
            <h2>ملاحظة مهمة للسيرفر</h2>
            <p class="muted">لا تستخدم أمر <strong>migrate:fresh</strong> على Hostinger لأنه يمسح كل الجداول والمستخدمين. استخدم فقط <strong>php artisan migrate --force</strong>.</p>
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
                            <span class="badge" data-table-status="{{ $table->id }}">{{ $statuses[$table->status] ?? $table->status }}</span>
                            <div style="margin-top:14px;" class="muted">{{ $table->seats ? $table->seats.' كرسي' : 'لم يحدد عدد الكراسي' }}</div>
                            @if($table->activeSession)
                                <div style="margin-top:8px;">{{ $table->activeSession->invoice_number }}</div>
                            @endif
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
            label.textContent = statusLabels[table.status] || table.status;
        });
    } catch (error) {
        console.warn('تعذر تحديث الطاولات', error);
    }
}
setInterval(refreshTables, 5000);
</script>
@endpush
