<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalDesign extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_booking_type_id',
        'name',
    ];

    public function externalBookingType()
    {
        return $this->belongsTo(ExternalBookingType::class,'external_booking_type_id');
    }

    public function items()
    {
        return $this->hasMany(ExternalBookingItem::class,'design_id');
    }
}