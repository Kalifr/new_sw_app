<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Rfq;
use App\Models\User;
use App\Notifications\NewRfqNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class RfqController extends Controller
{
    public function index(): Response
    {
        $rfqs = auth()->user()->type === 'seller' 
            ? Rfq::with(['buyer', 'product', 'quotes' => fn($q) => $q->where('seller_id', auth()->id())])
                ->whereHas('product', fn($q) => $q->where('user_id', auth()->id()))
                ->latest()
                ->paginate(10)
            : auth()->user()->rfqs()
                ->with(['product', 'quotes'])
                ->latest()
                ->paginate(10);

        return Inertia::render('Rfqs/Index', [
            'rfqs' => $rfqs
        ]);
    }

    public function create(Product $product): Response
    {
        return Inertia::render('Rfqs/Create', [
            'product' => $product->load('user'),
            'units' => [
                'weight' => ['kg', 'tonne', 'lb'],
                'volume' => ['l', 'ml', 'gal'],
                'quantity' => ['piece', 'box', 'container']
            ],
            'payment_terms' => [
                'L/C',
                'D/P',
                'T/T',
                'CAD',
                'Advance Payment'
            ],
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'specifications' => 'nullable|string',
            'packaging_requirements' => 'nullable|string',
            'shipping_requirements' => 'nullable|string',
            'quality_requirements' => 'nullable|string',
            'certification_requirements' => 'nullable|string',
            'target_delivery_date' => 'nullable|date',
            'target_price_range' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'delivery_location' => 'required|string',
            'valid_until' => 'required|date|after:today',
        ]);

        $rfq = auth()->user()->rfqs()->create([
            ...$validated,
            'product_id' => $product->id,
            'status' => 'open'
        ]);

        // Notify the seller
        $seller = $product->user;
        $seller->notify(new NewRfqNotification($rfq));

        return redirect()->route('rfqs.show', $rfq)
            ->with('success', 'RFQ created successfully.');
    }

    public function show(Rfq $rfq): Response
    {
        $this->authorize('view', $rfq);

        return Inertia::render('Rfqs/Show', [
            'rfq' => $rfq->load(['buyer', 'product', 'quotes.seller']),
        ]);
    }

    public function edit(Rfq $rfq): Response
    {
        $this->authorize('update', $rfq);

        return Inertia::render('Rfqs/Edit', [
            'rfq' => $rfq->load(['product', 'quotes']),
            'units' => [
                'weight' => ['kg', 'tonne', 'lb'],
                'volume' => ['l', 'ml', 'gal'],
                'quantity' => ['piece', 'box', 'container']
            ],
            'payment_terms' => [
                'L/C',
                'D/P',
                'T/T',
                'CAD',
                'Advance Payment'
            ],
        ]);
    }

    public function update(Request $request, Rfq $rfq)
    {
        $this->authorize('update', $rfq);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'specifications' => 'nullable|string',
            'packaging_requirements' => 'nullable|string',
            'shipping_requirements' => 'nullable|string',
            'quality_requirements' => 'nullable|string',
            'certification_requirements' => 'nullable|string',
            'target_delivery_date' => 'nullable|date',
            'target_price_range' => 'nullable|string',
            'payment_terms' => 'nullable|string',
            'delivery_location' => 'required|string',
            'valid_until' => 'required|date|after:today',
            'status' => 'required|in:open,closed,expired',
        ]);

        $rfq->update($validated);

        return back()->with('success', 'RFQ updated successfully.');
    }

    public function destroy(Rfq $rfq)
    {
        $this->authorize('delete', $rfq);

        $rfq->delete();

        return redirect()->route('rfqs.index')
            ->with('success', 'RFQ deleted successfully.');
    }

    public function close(Rfq $rfq)
    {
        $this->authorize('update', $rfq);

        $rfq->update(['status' => 'closed']);

        return back()->with('success', 'RFQ closed successfully.');
    }
} 