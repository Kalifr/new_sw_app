<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Illuminate\Support\Str;

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
        'slug',
        'meta_title',
        'meta_description',
        'canonical_url',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->generateSlug();
            $product->generateMetaTags();
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') || $product->isDirty('country_of_origin')) {
                $product->generateSlug();
            }
            $product->generateMetaTags();
        });
    }

    public function generateSlug()
    {
        $baseSlug = Str::slug($this->name);
        $category = $this->getMainCategory();
        $country = Str::slug($this->country_of_origin);
        
        $this->slug = "{$category}/{$country}/{$baseSlug}";
        
        // Ensure uniqueness
        $count = 2;
        while (static::where('slug', $this->slug)
                     ->where('id', '!=', $this->id ?? 0)
                     ->exists()) {
            $this->slug = "{$category}/{$country}/{$baseSlug}-{$count}";
            $count++;
        }
    }

    public function generateMetaTags()
    {
        // Generate SEO-friendly meta title
        $this->meta_title = sprintf(
            '%s from %s | %s | %s',
            $this->name,
            $this->country_of_origin,
            $this->variety ?? $this->getMainCategory(),
            config('app.name')
        );

        // Generate meta description
        $description = strip_tags($this->description);
        $this->meta_description = Str::limit(
            sprintf(
                '%s. %s from %s. Available quantity: %s %s. Price: %s %s per %s.',
                $description,
                $this->name,
                $this->country_of_origin,
                $this->quantity_available,
                $this->quantity_unit,
                $this->price,
                config('app.currency'),
                $this->price_unit
            ),
            160
        );

        // Set canonical URL
        $this->canonical_url = route('products.listing.detail', [
            $this->getMainCategory(),
            strtolower($this->country_of_origin),
            $this->slug
        ]);
    }

    public function getMainCategory()
    {
        return Str::slug(
            $this->search_categories[0] 
            ?? $this->category 
            ?? 'product'
        );
    }

    public function getJsonLd(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->id,
            'brand' => [
                '@type' => 'Brand',
                'name' => $this->user->organization_name ?? config('app.name'),
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('products.listing.detail', [
                    $this->getMainCategory(),
                    strtolower($this->country_of_origin),
                    $this->slug
                ]),
                'priceCurrency' => config('app.currency'),
                'price' => $this->price,
                'priceValidUntil' => $this->expiry_date?->format('Y-m-d') ?? now()->addYear()->format('Y-m-d'),
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability' => $this->quantity_available > 0 
                    ? 'https://schema.org/InStock' 
                    : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => $this->user->organization_name ?? $this->user->name,
                ],
            ],
            'image' => $this->mainImage?->path 
                ? [asset('storage/' . $this->mainImage->path)]
                : [],
            'countryOfOrigin' => $this->country_of_origin,
            'category' => $this->getMainCategory(),
        ];
    }

    public function getOpenGraphData(): array
    {
        return [
            'title' => $this->meta_title,
            'description' => $this->meta_description,
            'url' => route('products.listing.detail', [
                $this->getMainCategory(),
                strtolower($this->country_of_origin),
                $this->slug
            ]),
            'type' => 'product',
            'image' => $this->mainImage?->path 
                ? asset('storage/' . $this->mainImage->path)
                : null,
            'price:amount' => $this->price,
            'price:currency' => config('app.currency'),
        ];
    }

    public function getTwitterCardData(): array
    {
        return [
            'card' => 'product',
            'title' => $this->meta_title,
            'description' => $this->meta_description,
            'image' => $this->mainImage?->path 
                ? asset('storage/' . $this->mainImage->path)
                : null,
        ];
    }

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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include published products.
     */
    public function scopePublishedOld($query) 
    {
        return $query->where('is_published', true);
    }
}