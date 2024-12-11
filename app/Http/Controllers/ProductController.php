<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = auth()->user()->products()
            ->with('mainImage')
            ->latest()
            ->paginate(10);

        return Inertia::render('Products/Index', [
            'products' => $products
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
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
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'certifications' => 'nullable|array',
            'processing_level' => 'nullable|array',
            'payment_terms' => 'nullable|array',
            'delivery_terms' => 'nullable|array',
            'sample_available' => 'boolean',
            'available_months' => 'required|array',
            'images' => 'required|array|min:1',
            'images.*.file' => 'required|image|max:5120', // 5MB max
            'images.*.type' => 'required|in:main,gallery',
            'images.*.order' => 'required|integer|min:0',
        ]);

        $product = auth()->user()->products()->create([
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

        return redirect()->route('products.edit', $product)
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product): Response
    {
        $this->authorize('update', $product);

        return Inertia::render('Products/Edit', [
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
        $this->authorize('update', $product);

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
            'expiry_date' => 'nullable|date',
            'storage_conditions' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'certifications' => 'nullable|array',
            'processing_level' => 'nullable|array',
            'payment_terms' => 'nullable|array',
            'delivery_terms' => 'nullable|array',
            'sample_available' => 'boolean',
            'available_months' => 'required|array',
            'status' => 'required|in:draft,published,sold,expired',
            'new_images' => 'nullable|array',
            'new_images.*.file' => 'required|image|max:5120', // 5MB max
            'new_images.*.type' => 'required|in:main,gallery',
            'new_images.*.order' => 'required|integer|min:0',
            'deleted_images' => 'nullable|array',
            'deleted_images.*' => 'exists:product_images,id'
        ]);

        $product->update($validated);

        // Handle deleted images
        if ($request->has('deleted_images')) {
            $deletedImages = $product->images()->whereIn('id', $request->deleted_images)->get();
            foreach ($deletedImages as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->images()->whereIn('id', $request->deleted_images)->delete();
        }

        // Handle new images
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $image) {
                $path = $image['file']->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'type' => $image['type'],
                    'order' => $image['order']
                ]);
            }
        }

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Delete images from storage
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function publish(Product $product)
    {
        $this->authorize('update', $product);

        $product->update(['status' => 'published']);

        return back()->with('success', 'Product published successfully.');
    }
} 