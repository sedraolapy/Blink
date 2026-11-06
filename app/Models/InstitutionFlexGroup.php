<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;

class InstitutionFlexGroup extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'name',
    ];

    public array $translatable = [
        'name',
    ];

    public function billboards()
    {
        return $this->hasMany(FlexBillboard::class,'institution_flex_group_id');
    }


    protected static function booted(): void
    {
        static::deleting(function (self $institution): void {
            if ($institution->billboards()->exists()) {
                throw ValidationException::withMessages([
                    'institution' => __('filament/admin/institution_flex_group_resource.cannot_delete'),
                ]);
            }
        });
    }

}
