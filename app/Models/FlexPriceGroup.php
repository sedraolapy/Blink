<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlexPriceGroup extends Model
{
    protected $fillable = [
        'governorate_id',
        'billboards_count',
        'local_price',
        'foreign_price',
    ];

    protected function casts(): array
    {
        return [
            'billboards_count' => 'integer',
            'local_price' => 'decimal:2',
            'foreign_price' => 'decimal:2',
        ];
    }

    public function governorate()
    {
        return $this->belongsTo(Governorate::class);
    }
}
