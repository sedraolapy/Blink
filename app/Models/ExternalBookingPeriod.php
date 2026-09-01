<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ExternalBookingPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_booking_type_id',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function externalBookingType()
    {
        return $this->belongsTo(ExternalBookingType::class);
    }

    public function items()
    {
        return $this->hasMany(ExternalBookingItem::class);
    }
}