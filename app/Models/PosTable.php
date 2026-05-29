<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosTable extends Model
{
    use SoftDeletes;

    public const STATUS_FREE = 'free';

    public const STATUS_BUSY = 'busy';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_BILLING = 'billing';

    public const SECTION_INDOOR = 'indoor';

    public const SECTION_OUTDOOR = 'outdoor';

    public const SECTION_VIP = 'vip';

    protected $fillable = [
        'name',
        'section',
        'status',
        'seats',
        'sort_order',
    ];

    public function cafeSessions()
    {
        return $this->hasMany(CafeSession::class);
    }

    public function activeSession()
    {
        return $this->hasOne(CafeSession::class)->where('status', 'open')->latestOfMany();
    }
}
