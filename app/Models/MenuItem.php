<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    public const CATEGORY_SHISHA = 'shisha';
    public const CATEGORY_FOOD = 'food';
    public const CATEGORY_DRINK = 'drink';

    protected $fillable = [
        'name',
        'price',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
