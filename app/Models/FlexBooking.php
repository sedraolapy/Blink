<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'installation_order',
        'extension_order',
    ];

    protected function casts(): array
    {
        return [
            'installation_order' => 'boolean',
            'extension_order' => 'boolean',
        ];
    }


    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function periods()
    {
        return $this->hasMany(FlexBookingPeriod::class);
    }

    public function designs()
    {
        return $this->hasMany(FlexDesign::class);
    }
}
