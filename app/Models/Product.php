<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * fillable 
     * 
     * @var array
     */
    protected $fillable = [
        'image',
        'title',
        'slug',
        'category_id',
        'user_id',
        'description',
        'weight',
        'price',
        'stock',
        'discount'
    ];

    /**
     * Get the category that owns the product
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the reviews for the product
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get the formatted average rating for the review
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function reviewAvgRating(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? substr($value, 0, 3) : 0,
        );
    }
}
