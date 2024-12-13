<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::with(['mainImage', 'category'])
            ->latest()
            ->paginate(10);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'units' => [
                'weight' => ['kg', 'tonne', 'lb'],
                'volume' => ['l', 'ml', 'gal'],
                'quantity' => ['piece', 'box', 'container']
            ],
            'certifications' => [
                'Organic',
                'Fair Trade',
                'GAP',
                'ISO 22000',
                'HACCP',
                'Other'
            ],
            'processing_levels' => [
                'Raw',
                'Semi-processed',
                'Processed'
            ],
            'payment_terms' => [
                'L/C',
                'D/P',
                'T/T',
                'CAD',
                'Advance Payment'
            ],
            'delivery_terms' => [
                'FOB',
                'CIF',
                'EXW',
                'DAP',
                'DDP'
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'variety' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'growing_method' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string',
            'minimum_order' => 'required|numeric|min:0',
            'minimum_order_unit' => 'required|string',
            'quantity_available' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'country_of_origin' => 'required|string',
            'region' => 'nullable|string',
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:harvest_date',
            'storage_conditions' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'certifications' => 'nullable|array',
            'processing_level' => 'nullable|array',
            'payment_terms' => 'nullable|array',
            'delivery_terms' => 'nullable|array',
            'sample_available' => 'boolean',
            'available_months' => 'required|array',
            'images' => 'required|array|min:1',
            'images.*.file' => 'required|image|max:5120',
            'images.*.type' => 'required|in:main,gallery',
            'images.*.order' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create([
            ...$validated,
            'status' => 'draft'
        ]);

        foreach ($request->file('images') as $image) {
            $path = $image['file']->store('products', 'public');
            $product->images()->create([
                'path' => $path,
                'type' => $image['type'],
                'order' => $image['order']
            ]);
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            'product' => $product->load('images'),
            'units' => [
                'weight' => ['kg', 'tonne', 'lb'],
                'volume' => ['l', 'ml', 'gal'],
                'quantity' => ['piece', 'box', 'container']
            ],
            'certifications' => [
                'Organic',
                'Fair Trade',
                'GAP',
                'ISO 22000',
                'HACCP',
                'Other'
            ],
            'processing_levels' => [
                'Raw',
                'Semi-processed',
                'Processed'
            ],
            'payment_terms' => [
                'L/C',
                'D/P',
                'T/T',
                'CAD',
                'Advance Payment'
            ],
            'delivery_terms' => [
                'FOB',
                'CIF',
                'EXW',
                'DAP',
                'DDP'
            ]
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'variety' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
            'growing_method' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string',
            'minimum_order' => 'required|numeric|min:0',
            'minimum_order_unit' => 'required|string',
            'quantity_available' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'country_of_origin' => 'required|string',
            'region' => 'nullable|string',
            'harvest_date' => 'nullable|date',
            'expiry_date' => 'nullable|date|after:harvest_date',
            'storage_conditions' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'certifications' => 'nullable|array',
            'processing_level' => 'nullable|array',
            'payment_terms' => 'nullable|array',
            'delivery_terms' => 'nullable|array',
            'sample_available' => 'boolean',
            'available_months' => 'required|array',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product->update($validated);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image['file']->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'type' => $image['type'],
                    'order' => $image['order']
                ]);
            }
        }

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function publish(Product $product)
    {
        $product->update(['status' => 'published']);

        return back()->with('success', 'Product published successfully.');
    }
} 