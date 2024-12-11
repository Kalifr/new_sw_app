<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'variety',
        'grade',
        'growing_method',
        'price',
        'price_unit',
        'minimum_order',
        'minimum_order_unit',
        'quantity_available',
        'quantity_unit',
        'country_of_origin',
        'region',
        'harvest_date',
        'expiry_date',
        'storage_conditions',
        'packaging_details',
        'certifications',
        'processing_level',
        'payment_terms',
        'delivery_terms',
        'sample_available',
        'available_months',
        'status',
    ];

    protected $casts = [
        'certifications' => 'array',
        'processing_level' => 'array',
        'payment_terms' => 'array',
        'delivery_terms' => 'array',
        'available_months' => 'array',
        'harvest_date' => 'date',
        'expiry_date' => 'date',
        'sample_available' => 'boolean',
        'price' => 'decimal:2',
        'minimum_order' => 'decimal:2',
        'quantity_available' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function mainImage()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'main');
    }

    public function galleryImages()
    {
        return $this->hasMany(ProductImage::class)
            ->where('type', 'gallery')
            ->orderBy('order');
    }
} 