@extends('layouts.app')

@section('title', 'المنيو')

@section('content')
<section class="panel">
    <h1>إدارة المنيو</h1>
    <form method="POST" action="{{ route('menu-items.store') }}" class="grid grid-4">
        @csrf
        <input class="field" name="name" placeholder="اسم الصنف" required>
        <input class="field" type="number" name="price" step="0.01" min="0" placeholder="السعر" required>
        <select name="category" required>
            @foreach($categories as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary" type="submit">إضافة صنف</button>
    </form>
</section>

<section class="panel" style="margin-top:18px;">
    <h2>الأصناف</h2>
    <div class="grid">
        @foreach($items as $item)
            <div class="order-card" style="grid-template-columns:1.4fr .6fr .7fr .5fr auto;">
                <form method="POST" action="{{ route('menu-items.update', $item) }}" style="display:contents;">
                    @csrf
                    @method('PATCH')
                    <input class="field" name="name" value="{{ $item->name }}">
                    <input class="field" type="number" step="0.01" min="0" name="price" value="{{ $item->price }}">
                    <select name="category">
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" @selected($item->category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <label style="display:flex;gap:6px;align-items:center;"><input type="checkbox" name="is_active" value="1" @checked($item->is_active)> فعال</label>
                    <button class="btn btn-small" type="submit">حفظ</button>
                </form>
                <form method="POST" action="{{ route('menu-items.destroy', $item) }}" style="grid-column:1 / -1;">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger btn-small" type="submit">تعطيل</button>
                </form>
            </div>
        @endforeach
    </div>
</section>
@endsection
