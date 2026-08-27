<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class FlexBillboard extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'area_id',
        'location_name',
        'latitude',
        'longitude',
        'width',
        'height',
        'local_price',
        'foreign_price',
    ];

    public array $translatable = [
        'location_name',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('flex_billboard_video')
            ->singleFile();
    }
}
