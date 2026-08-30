<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class Area extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'governorate_id',
        'name',
    ];

    public array $translatable = [
        'name',
    ];

    public function governorate(): BelongsTo
    {
        return $this->belongsTo(Governorate::class);
    }

    public function flexBillboards()
    {
        return $this->hasMany(FlexBillboard::class);
    }

    public function ledScreens()
    {
        return $this->hasMany(LedScreen::class);
    }

    public function externalAssets()
    {
        return $this->hasMany(ExternalAsset::class);
    }
}