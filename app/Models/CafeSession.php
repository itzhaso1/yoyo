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
        return $this->relatedTable()?->name ?? 'طاولة محذوفة';
    }

    public function tableStatus(): string
    {
        return $this->relatedTable()?->status ?? 'free';
    }

    private function relatedTable(): ?PosTable
    {
        return $this->relationLoaded('table')
            ? $this->getRelation('table')
            : $this->table()->first();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function recalculateTotals(): void
    {
        if ($this->isCancelled()) {
            $this->subtotal = 0;
            $this->discount = 0;
            $this->tip = 0;
            $this->total_price = 0;
            $this->save();

            return;
        }

        $subtotal = $this->orderItems->sum(
            fn (OrderItem $item): float => (float) $item->price * (int) $item->quantity
        );

        $this->subtotal = $subtotal;
        $this->total_price = max(0, $subtotal - (float) $this->discount + (float) $this->tip);
        $this->save();
    }
}
