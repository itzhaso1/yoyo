@extends('layouts.app')

@section('title', 'تسجيل الدخول - بيت جدي')

@section('content')
<div class="panel" style="max-width:460px;margin:70px auto;">
    <h1>تسجيل الدخول</h1>
    <p class="muted">يمكنك الدخول بالبريد الإلكتروني أو اسم المستخدم.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="form-row">
            <label>البريد الإلكتروني أو اسم المستخدم</label>
            <input class="field" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="مثال: admin@example.com أو admin">
        </div>
        <div class="form-row">
            <label>كلمة المرور</label>
            <input class="field" type="password" name="password" required autocomplete="current-password">
        </div>
        <label style="display:flex;gap:8px;align-items:center;margin-bottom:16px;">
            <input type="checkbox" name="remember" value="1"> تذكرني
        </label>
        <button class="btn btn-primary" type="submit" style="width:100%;">دخول</button>
    </form>
    <p class="muted" style="margin-top:18px;">حساب الأدمن: admin@example.com أو admin / كلمة المرور: admin123</p>
</div>
@endsection
