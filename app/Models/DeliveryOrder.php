<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'phone',
        'location',
        'item_name',
        'quantity',
        'price',
        'total_price',
        'notes',
        'status',
        'ordered_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'ordered_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
