<?php

namespace App\Models;

use App\Enums\ExternalAssetTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class ExternalAsset extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    protected $fillable = [
        'code',
        'type',
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

    protected function casts(): array
    {
        return [
            'type' => ExternalAssetTypeEnum::class,
        ];
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('external_asset_video')
            ->singleFile();
    }
}
