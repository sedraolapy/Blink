<?php

namespace App\Models;

use App\Enums\DisplayGroupEnum;
use App\Enums\InstallationDayEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Governorate extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
        'display_group',
        'installation_day',
    ];

    public array $translatable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'display_group' => DisplayGroupEnum::class,
            'installation_day' => InstallationDayEnum::class,
        ];
    }

    public function areas()
    {
        return $this->hasMany(Area::class);
    }

    public function flexPriceGroup()
    {
        return $this->hasOne(FlexPriceGroup::class);
    }
}
