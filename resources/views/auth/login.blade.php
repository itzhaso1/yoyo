@extends('layouts.app')

@section('title', 'Login - Cafe POS')

@section('content')
<div class="panel" style="max-width:460px;margin:70px auto;">
    <h1>تسجيل الدخول</h1>
    <p class="muted">ادخل باسم المستخدم وكلمة المرور للوصول إلى نظام الكاشير.</p>
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div class="form-row">
            <label>اسم المستخدم</label>
            <input class="field" name="username" value="{{ old('username') }}" required autofocus autocomplete="username">
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
    <p class="muted" style="margin-top:18px;">بيانات أولية بعد تشغيل seed: admin/admin123 أو cashier/cashier123</p>
</div>
@endsection
