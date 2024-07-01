<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    /**
     * fillable 
     * 
     * @var array
     */
    protected $fillable = [
        'image', 
        'link'
    ];

    /**
     * Get the URL for the slider's image.
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => url('/storage/sliders/' . $value),
        );
    }
}
