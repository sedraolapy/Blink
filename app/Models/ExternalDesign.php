<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_booking_id',
        'name',
    ];

    public function externalBooking()
    {
        return $this->belongsTo(ExternalBooking::class);
    }

    public function items()
    {
        return $this->hasMany(ExternalBookingItem::class,'design_id');
    }
}