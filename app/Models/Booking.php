<?php

namespace App\Models;

use App\Enums\BookingTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'booking_type',
        'start_date',
        'end_date',
        'installation_order',
        'extension_order',
        'operation_order',
    ];

    protected function casts(): array
    {
        return [
            'booking_type' => BookingTypeEnum::class,

            'start_date' => 'date',
            'end_date' => 'date',

            'installation_order' => 'boolean',
            'extension_order' => 'boolean',
            'operation_order' => 'boolean',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class);
    }

    public function flexBooking()
    {
        return $this->hasOne(FlexBooking::class);
    }

    public function ledBooking()
    {
        return $this->hasOne(LedBooking::class);
    }

    public function externalBooking()
    {
        return $this->hasOne(ExternalBooking::class);
    }

    public function requiresInstallationAndExtension(): bool
    {
        $hasFlex = $this->relationLoaded('flexBooking')
            ? $this->flexBooking !== null
            : $this->flexBooking()->exists();

        $hasExternal = $this->relationLoaded('externalBooking')
            ? $this->externalBooking !== null
            : $this->externalBooking()->exists();

        return $hasFlex || $hasExternal;
    }

    public function requiresOperation(): bool
    {
        return $this->relationLoaded('ledBooking')
            ? $this->ledBooking !== null
            : $this->ledBooking()->exists();
    }

    public function scopeDateRange(Builder $query,?string $fromDate,?string $toDate): Builder
    {
        if ($fromDate && $toDate) {
            return $query
                ->whereDate('start_date', '<=', $toDate)
                ->whereDate('end_date', '>=', $fromDate);
        }

        if ($fromDate) {
            return $query
                ->whereDate('end_date', '>=', $fromDate);
        }

        if ($toDate) {
            return $query
                ->whereDate('start_date', '<=', $toDate);
        }

        return $query;
    }
}
