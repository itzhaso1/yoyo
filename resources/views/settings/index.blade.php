@extends('layouts.app')

@section('title', 'الإعدادات - بيت جدي')

@section('content')
<div class="grid grid-2">
    <section class="panel">
        <h1>الإعدادات</h1>
        <p class="muted">هنا تجد إعدادات الحساب والطاولات بدلاً من ازدحام لوحة الكاشير الرئيسية.</p>
    </section>

    <section class="panel">
        <h2>تغيير كلمة المرور</h2>
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
            <p class="muted">لا تستخدم أمر <strong>migrate:fresh</strong> على Hostinger لأنه يمسح كل الجداول والمستخدمين والفواتير. استخدم فقط <strong>php artisan migrate --force</strong>.</p>
        </section>
    @endif
</div>
@endsection
