<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, SoftDeletes, Searchable;

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
        'search_vector',
        'search_metadata',
        'price_min',
        'price_max',
        'available_locations',
        'search_categories',
        'category_id'
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
        'search_metadata' => 'array',
        'available_locations' => 'array',
        'search_categories' => 'array',
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

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'variety' => $this->variety,
            'grade' => $this->grade,
            'growing_method' => $this->growing_method,
            'price' => $this->price,
            'price_unit' => $this->price_unit,
            'country_of_origin' => $this->country_of_origin,
            'region' => $this->region,
            'certifications' => $this->certifications,
            'processing_level' => $this->processing_level,
            'search_vector' => $this->search_vector,
            'search_metadata' => $this->search_metadata,
            'price_min' => $this->price_min,
            'price_max' => $this->price_max,
            'available_locations' => $this->available_locations,
            'search_categories' => $this->search_categories,
            'status' => $this->status,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_of_origin', 'id');
    }
} 