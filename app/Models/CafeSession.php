<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CafeSession extends Model
{
    protected $fillable = [
        'user_id',
        'pos_table_id',
        'invoice_number',
        'status',
        'opened_at',
        'closed_at',
        'subtotal',
        'discount',
        'tip',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tip' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function table()
    {
        return $this->belongsTo(PosTable::class, 'pos_table_id')->withTrashed();
    }

    public function tableName(): string
    {
        return $this->table?->name ?? 'طاولة محذوفة';
    }

    public function tableStatus(): string
    {
        return $this->table?->status ?? 'free';
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->orderItems->sum(
            fn (OrderItem $item): float => (float) $item->price * (int) $item->quantity
        );

        $this->subtotal = $subtotal;
        $this->total_price = max(0, $subtotal - (float) $this->discount + (float) $this->tip);
        $this->save();
    }
}
