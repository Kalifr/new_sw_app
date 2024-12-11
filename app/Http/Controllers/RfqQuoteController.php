<?php

namespace App\Http\Controllers;

use App\Models\Rfq;
use App\Models\RfqQuote;
use App\Notifications\NewQuoteNotification;
use App\Notifications\QuoteStatusUpdatedNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfqQuoteController extends Controller
{
    public function create(Rfq $rfq): Response
    {
        $this->authorize('createQuote', $rfq);

        return Inertia::render('RfqQuotes/Create', [
            'rfq' => $rfq->load(['buyer', 'product']),
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

    public function store(Request $request, Rfq $rfq)
    {
        $this->authorize('createQuote', $rfq);

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'specifications' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'shipping_details' => 'nullable|string',
            'quality_certifications' => 'nullable|string',
            'delivery_date' => 'required|date',
            'payment_terms' => 'required|string',
            'additional_notes' => 'nullable|string',
            'valid_until' => 'required|date|after:today',
        ]);

        $quote = $rfq->quotes()->create([
            ...$validated,
            'seller_id' => auth()->id(),
            'status' => 'pending'
        ]);

        // Notify the buyer
        $rfq->buyer->notify(new NewQuoteNotification($quote));

        return redirect()->route('rfqs.show', $rfq)
            ->with('success', 'Quote submitted successfully.');
    }

    public function edit(RfqQuote $quote): Response
    {
        $this->authorize('update', $quote);

        return Inertia::render('RfqQuotes/Edit', [
            'quote' => $quote->load(['rfq.buyer', 'rfq.product']),
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

    public function update(Request $request, RfqQuote $quote)
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'price' => 'required|numeric|min:0',
            'price_unit' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'quantity_unit' => 'required|string',
            'specifications' => 'nullable|string',
            'packaging_details' => 'nullable|string',
            'shipping_details' => 'nullable|string',
            'quality_certifications' => 'nullable|string',
            'delivery_date' => 'required|date',
            'payment_terms' => 'required|string',
            'additional_notes' => 'nullable|string',
            'valid_until' => 'required|date|after:today',
        ]);

        $quote->update($validated);

        return back()->with('success', 'Quote updated successfully.');
    }

    public function destroy(RfqQuote $quote)
    {
        $this->authorize('delete', $quote);

        $quote->delete();

        return redirect()->route('rfqs.show', $quote->rfq)
            ->with('success', 'Quote deleted successfully.');
    }

    public function updateStatus(Request $request, RfqQuote $quote)
    {
        $this->authorize('updateStatus', $quote);

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $quote->update($validated);

        // Notify the seller
        $quote->seller->notify(new QuoteStatusUpdatedNotification($quote));

        // If quote is accepted, close the RFQ
        if ($quote->isAccepted()) {
            $quote->rfq->update(['status' => 'closed']);
            
            // Reject all other quotes
            $quote->rfq->quotes()
                ->where('id', '!=', $quote->id)
                ->update(['status' => 'rejected']);
        }

        return back()->with('success', 'Quote status updated successfully.');
    }
} 