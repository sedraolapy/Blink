<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlexDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'flex_booking_id',
        'name',
    ];

    public function flexBooking()
    {
        return $this->belongsTo(FlexBooking::class);
    }

    public function items()
    {
        return $this->hasMany(FlexBookingItem::class, 'design_id');
    }
}