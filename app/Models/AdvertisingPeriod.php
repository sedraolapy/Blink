<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class AdvertisingPeriod extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'number',
    ];


    public function ranges(): HasMany
    {
        return $this->hasMany(AdvertisingPeriodRange::class);
    }
}