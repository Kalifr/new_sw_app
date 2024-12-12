<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('Search/Index', [
            'categories' => config('product-categories'),
            'locations' => config('locations'),
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:255',
            'categories' => 'nullable|array',
            'locations' => 'nullable|array',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|gt:price_min',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = Product::search($validated['query'] ?? '')
            ->when($validated['categories'] ?? null, function ($query, $categories) {
                return $query->whereIn('search_categories', $categories);
            })
            ->when($validated['locations'] ?? null, function ($query, $locations) {
                return $query->whereIn('available_locations', $locations);
            })
            ->when($validated['price_min'] ?? null, function ($query, $min) {
                return $query->where('price_min', '>=', $min);
            })
            ->when($validated['price_max'] ?? null, function ($query, $max) {
                return $query->where('price_max', '<=', $max);
            });

        $results = $query->paginate(12);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $results->items(),
                'meta' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total(),
                ],
            ]);
        }

        return Inertia::render('Search/Results', [
            'results' => $results,
            'filters' => $validated,
        ]);
    }

    public function suggestions(Request $request)
    {
        $query = $request->input('query');
        
        if (empty($query)) {
            return response()->json([]);
        }

        $suggestions = Product::search($query)
            ->take(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->search_categories[0] ?? null,
                    'image' => $product->mainImage?->path,
                    'url' => route('products.show', $product),
                ];
            });

        return response()->json($suggestions);
    }

    public function createRfqFromSearch(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'specifications' => 'nullable|string',
            'delivery_location' => 'required|string',
            'target_delivery_date' => 'nullable|date|after:today',
            'target_price_range' => 'nullable|string',
        ]);

        // Create a temporary product for the RFQ
        $product = Product::create([
            'name' => $validated['product_name'],
            'description' => $validated['specifications'] ?? '',
            'user_id' => $request->user()->id,
            'status' => 'draft',
        ]);

        // Create the RFQ
        $rfq = $request->user()->rfqs()->create([
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'quantity_unit' => $validated['quantity_unit'],
            'specifications' => $validated['specifications'],
            'delivery_location' => $validated['delivery_location'],
            'target_delivery_date' => $validated['target_delivery_date'],
            'target_price_range' => $validated['target_price_range'],
            'status' => 'open',
            'valid_until' => now()->addDays(30),
        ]);

        return redirect()->route('rfqs.show', $rfq)
            ->with('success', 'Your RFQ has been created successfully.');
    }
} 