<?php

namespace App\Models;

use App\Enums\ExternalAssetTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalBookingType extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_booking_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => ExternalAssetTypeEnum::class,
        ];
    }

    public function externalBooking()
    {
        return $this->belongsTo(ExternalBooking::class);
    }

    public function periods()
    {
        return $this->hasMany(ExternalBookingPeriod::class);
    }

    public function designs()
    {
        return $this->hasMany(ExternalDesign::class,'external_booking_type_id');
    }
}