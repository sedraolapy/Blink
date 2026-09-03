<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Quotation extends Model
{
    protected $fillable = [
        'booking_id',
        'calculated_total',
        'final_total',
    ];

    protected function casts(): array
    {
        return [
            'calculated_total' => 'decimal:2',
            'final_total' => 'decimal:2',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}