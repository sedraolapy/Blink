<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class LedNetwork extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'location_name',
        'local_price',
        'foreign_price',
    ];

    public array $translatable = [
        'location_name',
    ];

    public function screens()
    {
        return $this->hasMany(LedScreen::class, 'network_id');
    }
}