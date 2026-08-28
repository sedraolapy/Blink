<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class LedScreen extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'area_id',
        'network_id',
        'location_name',
        'latitude',
        'longitude',
        'width',
        'height',
        'width_px',
        'height_px',
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

    public function network()
    {
        return $this->belongsTo(LedNetwork::class, 'network_id');
    }
    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('led_screen_video')
            ->singleFile();
    }
}