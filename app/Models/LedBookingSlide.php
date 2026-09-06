<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedBookingSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'led_booking_item_id',
        'led_screen_id',
        'design_id',
        'slide_number',
    ];

    protected function casts(): array
    {
        return [
            'slide_number' => 'integer',
        ];
    }

    public function item()
    {
        return $this->belongsTo(LedBookingItem::class,'led_booking_item_id');
    }

    public function design()
    {
        return $this->belongsTo(LedDesign::class,'design_id');
    }

    public function screen()
    {
        return $this->belongsTo(LedScreen::class,'led_screen_id');
    }
}