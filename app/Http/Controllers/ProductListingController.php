<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductListingController extends Controller
{
    public function __construct()
    {
        // No auth middleware needed for public routes
    }

    public function index()
    {
        $products = Product::published()
            ->with(['mainImage', 'category', 'country'])
            ->latest()
            ->paginate(12);

        $categories = Category::withCount(['products' => function ($query) {
            $query->published();
        }])->get();

        return Inertia::render('Products/Index', [
            'products' => $products,
            'categories' => $categories,
            'featuredProducts' => $products,
            'meta' => [
                'title' => 'Browse Products - Your Marketplace',
                'description' => 'Discover quality products from verified suppliers worldwide.',
                'keywords' => 'products, marketplace, global trade, suppliers',
            ],
        ]);
    }

    public function category($category)
    {
        $products = Product::published()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category);
            })
            ->with(['category', 'images'])
            ->paginate(12);

        $categoryModel = \App\Models\Category::where('slug', $category)->firstOrFail();

        return Inertia::render('Products/CategoryListing', [
            'products' => $products,
            'category' => $categoryModel,
            'meta' => [
                'title' => $categoryModel->meta_title ?? $categoryModel->name . ' Products',
                'description' => $categoryModel->meta_description ?? "Browse our collection of {$categoryModel->name} products",
                'keywords' => $categoryModel->meta_keywords ?? "{$categoryModel->name}, products, marketplace",
            ],
        ]);
    }

    public function country($category, $country)
    {
        $products = Product::published()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category);
            })
            ->whereHas('country', function ($query) use ($country) {
                $query->where('slug', $country);
            })
            ->with(['category', 'country', 'images'])
            ->paginate(12);

        $categoryModel = \App\Models\Category::where('slug', $category)->firstOrFail();
        $countryModel = \App\Models\Country::where('slug', $country)->firstOrFail();

        return Inertia::render('Products/CountryListing', [
            'products' => $products,
            'category' => $categoryModel,
            'country' => $countryModel,
            'meta' => [
                'title' => "{$categoryModel->name} Products from {$countryModel->name}",
                'description' => "Browse {$categoryModel->name} products from {$countryModel->name}. Quality products from verified suppliers.",
                'keywords' => "{$categoryModel->name}, {$countryModel->name}, products, marketplace",
            ],
        ]);
    }

    public function show($category, $country, $slug)
    {
        $product = Product::published()
            ->whereHas('category', function ($query) use ($category) {
                $query->where('slug', $category);
            })
            ->whereHas('country', function ($query) use ($country) {
                $query->where('slug', $country);
            })
            ->where('slug', $slug)
            ->with(['category', 'country', 'images', 'specifications'])
            ->firstOrFail();

        return Inertia::render('Products/Show', [
            'product' => $product,
            'meta' => [
                'title' => $product->meta_title ?? $product->name,
                'description' => $product->meta_description ?? substr(strip_tags($product->description), 0, 160),
                'keywords' => $product->meta_keywords ?? "{$product->name}, {$product->category->name}, {$product->country->name}",
            ],
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => $product->name,
                'description' => strip_tags($product->description),
                'image' => $product->images->first() ? asset('storage/' . $product->images->first()->path) : null,
                'brand' => [
                    '@type' => 'Brand',
                    'name' => $product->brand ?? 'Unknown',
                ],
                'offers' => [
                    '@type' => 'Offer',
                    'availability' => 'https://schema.org/InStock',
                    'price' => $product->price ?? '0',
                    'priceCurrency' => 'USD',
                ],
            ],
        ]);
    }

    public function generateSitemap()
    {
        $products = Product::published()
            ->with(['category', 'country'])
            ->get();

        $categories = \App\Models\Category::has('products')->get();
        $countries = \App\Models\Country::has('products')->get();

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');

        // Add category pages
        foreach ($categories as $category) {
            $url = $xml->addChild('url');
            $url->addChild('loc', url($category->slug));
            $url->addChild('changefreq', 'daily');
            $url->addChild('priority', '0.8');
        }

        // Add category + country pages
        foreach ($categories as $category) {
            foreach ($countries as $country) {
                if (Product::published()
                    ->whereHas('category', function ($query) use ($category) {
                        $query->where('id', $category->id);
                    })
                    ->whereHas('country', function ($query) use ($country) {
                        $query->where('id', $country->id);
                    })
                    ->exists()) {
                    $url = $xml->addChild('url');
                    $url->addChild('loc', url("{$category->slug}/{$country->slug}"));
                    $url->addChild('changefreq', 'daily');
                    $url->addChild('priority', '0.7');
                }
            }
        }

        // Add product detail pages
        foreach ($products as $product) {
            $url = $xml->addChild('url');
            $url->addChild('loc', url("{$product->category->slug}/{$product->country->slug}/{$product->slug}"));
            $url->addChild('lastmod', $product->updated_at->toAtomString());
            $url->addChild('changefreq', 'weekly');
            $url->addChild('priority', '0.6');
        }

        return response($xml->asXML(), 200)
            ->header('Content-Type', 'application/xml');
    }
} 