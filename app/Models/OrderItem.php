<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'cafe_session_id',
        'menu_item_id',
        'item_name',
        'price',
        'quantity',
    ];

    protected $appends = [
        'line_total',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function cafeSession()
    {
        return $this->belongsTo(CafeSession::class);
    }

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class);
    }

    protected function lineTotal(): Attribute
    {
        return Attribute::get(fn (): float => (float) $this->price * (int) $this->quantity);
    }
}
