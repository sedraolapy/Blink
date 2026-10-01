<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingYear extends Model
{
    protected $fillable = [
        'year',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    protected static function booted(): void
    {

        static::deleting(function (WorkingYear $workingYear) {
            if ((int) $workingYear->year === now()->year) {
                return false;
            }
        });
    }
}
