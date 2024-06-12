<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    /**
     * fillable 
     * 
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'image'
    ];

    /**
     * Get the products for the category
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the URL for the category's image.
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function image(): Attribute
    {
        return Attribute::make(
            get: fn($value) => url('/storage/categories/' . $value),
        );
    }
}
