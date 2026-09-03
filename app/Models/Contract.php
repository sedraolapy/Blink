<?php

namespace App\Models;

use App\Enums\ContractStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Contract extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'booking_id',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => ContractStatusEnum::class,
        ];
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('contract_images');
    }
}
