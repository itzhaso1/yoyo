<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'بيت جدي')</title>
    <style>
        :root { --bg:#0f172a; --panel:#111827; --muted:#94a3b8; --text:#f8fafc; --card:#1f2937; --line:#334155; --primary:#38bdf8; --danger:#ef4444; --ok:#22c55e; --warn:#f59e0b; --billing:#3b82f6; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: Tahoma, Arial, sans-serif; background:linear-gradient(135deg,#0f172a,#111827); color:var(--text); }
        a { color:inherit; text-decoration:none; }
        input, select, button { font:inherit; }
        .topbar { display:flex; justify-content:space-between; align-items:center; padding:16px 28px; border-bottom:1px solid var(--line); background:rgba(15,23,42,.9); position:sticky; top:0; z-index:10; }
        .brand { font-weight:800; letter-spacing:.5px; }
        .nav { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
        .nav a, .nav button, .btn { border:1px solid var(--line); background:#172033; color:var(--text); border-radius:12px; padding:10px 14px; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
        .nav a:hover, .nav button:hover, .btn:hover { border-color:var(--primary); }
        .btn-primary { background:var(--primary); color:#062033; border-color:var(--primary); font-weight:700; }
        .btn-danger { background:var(--danger); border-color:var(--danger); }
        .btn-small { padding:7px 10px; border-radius:9px; font-size:13px; }
        .container { width:min(1280px, 94vw); margin:28px auto; }
        .panel { background:rgba(31,41,55,.86); border:1px solid var(--line); border-radius:20px; padding:20px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
        .grid { display:grid; gap:16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; }
        .stat { background:#101827; border:1px solid var(--line); border-radius:16px; padding:16px; }
        .stat strong { display:block; font-size:28px; margin-top:8px; }
        .muted { color:var(--muted); }
        .alert { padding:12px 14px; border-radius:12px; margin-bottom:16px; }
        .alert-ok { background:rgba(34,197,94,.15); border:1px solid rgba(34,197,94,.4); }
        .alert-error { background:rgba(239,68,68,.16); border:1px solid rgba(239,68,68,.45); }
        .form-row { display:grid; gap:8px; margin-bottom:12px; }
        .field, select { width:100%; border:1px solid var(--line); border-radius:12px; background:#0b1220; color:var(--text); padding:11px 12px; }
        .table-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:14px; }
        .table-card { min-height:132px; width:100%; border:0; color:white; border-radius:18px; padding:16px; text-align:right; cursor:pointer; box-shadow: inset 0 0 0 1px rgba(255,255,255,.18), 0 16px 28px rgba(0,0,0,.22); transition:.16s transform,.16s filter; }
        .table-card:hover { transform:translateY(-2px); filter:brightness(1.08); }
        .status-free { background:linear-gradient(135deg,#16a34a,#22c55e); }
        .status-busy { background:linear-gradient(135deg,#b91c1c,#ef4444); }
        .status-reserved { background:linear-gradient(135deg,#b45309,#f59e0b); }
        .status-billing { background:linear-gradient(135deg,#1d4ed8,#3b82f6); }
        .badge { display:inline-flex; padding:5px 9px; border-radius:999px; background:rgba(255,255,255,.16); font-size:12px; }
        .legend { display:flex; gap:10px; flex-wrap:wrap; margin:12px 0 18px; }
        .legend span { display:inline-flex; align-items:center; gap:7px; color:var(--muted); }
        .dot { width:13px; height:13px; border-radius:99px; display:inline-block; }
        .order-card { display:grid; grid-template-columns: 1.4fr .7fr .6fr .7fr auto; gap:8px; align-items:center; padding:10px; border:1px solid var(--line); border-radius:14px; background:#101827; margin-bottom:10px; }
        .menu-button { text-align:right; width:100%; border:1px solid var(--line); border-radius:14px; background:#101827; color:var(--text); padding:12px; cursor:pointer; }
        .menu-button:hover { border-color:var(--primary); }
        .invoice { background:white; color:#111827; width:360px; max-width:100%; margin:20px auto; padding:22px; border-radius:10px; }
        .invoice table, .data-table { width:100%; border-collapse:collapse; }
        .invoice th, .invoice td, .data-table th, .data-table td { border-bottom:1px solid #334155; padding:10px; text-align:right; }
        .data-table th { color:#cbd5e1; }
        @media (max-width: 900px) { .grid-2,.grid-3,.grid-4,.stats { grid-template-columns:1fr; } .order-card { grid-template-columns:1fr; } .topbar { align-items:flex-start; flex-direction:column; gap:12px; } }
        @media print { body { background:white; } .no-print { display:none !important; } .invoice { box-shadow:none; margin:0 auto; } }
    </style>
    @stack('head')
</head>
<body>
    <header class="topbar no-print">
        <a href="{{ route('dashboard') }}" class="brand">بيت جدي</a>
        @auth
            <nav class="nav">
                @php($roleLabel = ['admin' => 'مدير', 'cashier' => 'كاشير'][auth()->user()->role] ?? auth()->user()->role)
                <span class="muted">{{ auth()->user()->name }} ({{ $roleLabel }})</span>
                <a href="{{ route('dashboard') }}">الطاولات</a>
                <a href="{{ route('delivery-orders.index') }}">طلبات خارجية</a>
                <a href="{{ route('settings.index') }}">الإعدادات</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('menu-items.index') }}">إدارة الأصناف</a>
                    <a href="{{ route('reports.daily') }}">التقارير اليومية</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">تسجيل الخروج</button>
                </form>
            </nav>
        @endauth
    </header>

    <main class="container">
        @if(session('status'))
            <div class="alert alert-ok no-print">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error no-print">
                <strong>يرجى تصحيح الأخطاء:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
