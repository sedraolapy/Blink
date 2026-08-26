<?php

namespace App\Models;

use App\Enums\DisplayGroupEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvertisingPeriodRange extends Model
{
    protected $fillable = [
        'advertising_period_id',
        'display_group',
        'start_month',
        'start_day',
        'end_month',
        'end_day',
    ];

    protected function casts(): array
    {
        return [
            'display_group' => DisplayGroupEnum::class,
        ];
    }

    public function advertisingPeriod(): BelongsTo
    {
        return $this->belongsTo(AdvertisingPeriod::class);
    }
}
