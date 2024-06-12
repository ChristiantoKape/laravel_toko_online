<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    /**
     * fillable 
     * 
     * @var array
     */
    protected $fillable = [
        'province_id',
        'city_id',
        'name'
    ];

    /**
     * Get the province that owns the city
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }
}
