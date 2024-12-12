<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\RfqQuote;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Order::query()
            ->with(['buyer:id,name', 'seller:id,name', 'rfq.product'])
            ->when($request->user()->isBuyer(), function ($query) use ($request) {
                $query->where('buyer_id', $request->user()->id);
            })
            ->when($request->user()->isSeller(), function ($query) use ($request) {
                $query->where('seller_id', $request->user()->id);
            })
            ->when($request->user()->isInspector(), function ($query) use ($request) {
                $query->whereIn('status', [
                    Order::STATUS_INSPECTION_PENDING,
                    Order::STATUS_INSPECTION_PASSED
                ]);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();

        return Inertia::render('Orders/Index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['status']),
            'statuses' => [
                Order::STATUS_DRAFT => 'Draft',
                Order::STATUS_PROFORMA_ISSUED => 'Proforma Issued',
                Order::STATUS_PAYMENT_PENDING => 'Payment Pending',
                Order::STATUS_PAYMENT_VERIFIED => 'Payment Verified',
                Order::STATUS_IN_PREPARATION => 'In Preparation',
                Order::STATUS_INSPECTION_PENDING => 'Inspection Pending',
                Order::STATUS_INSPECTION_PASSED => 'Inspection Passed',
                Order::STATUS_READY_FOR_SHIPPING => 'Ready for Shipping',
                Order::STATUS_SHIPPED => 'Shipped',
                Order::STATUS_DELIVERED => 'Delivered',
                Order::STATUS_COMPLETED => 'Completed',
                Order::STATUS_CANCELLED => 'Cancelled',
            ],
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load([
            'buyer:id,name',
            'seller:id,name',
            'rfq.product',
            'quote',
            'documents' => function ($query) {
                $query->latest();
            },
            'paymentRecords' => function ($query) {
                $query->with('verifier:id,name')->latest();
            },
            'inspectionRecords' => function ($query) {
                $query->with('inspector:id,name')->latest();
            },
            'statusHistory' => function ($query) {
                $query->with('user:id,name')->latest();
            },
        ]);

        return Inertia::render('Orders/Show', [
            'order' => $order,
            'canEdit' => $order->canBeEdited(),
            'canCancel' => $order->canBeCancelled(),
            'canInspect' => request()->user()->canInspect($order),
            'canVerifyPayment' => request()->user()->isAdmin(),
        ]);
    }

    public function store(Request $request, RfqQuote $quote)
    {
        $this->authorize('create-order', $quote);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'buyer_id' => $quote->rfq->buyer_id,
                'seller_id' => $quote->seller_id,
                'rfq_id' => $quote->rfq_id,
                'quote_id' => $quote->id,
                'status' => Order::STATUS_DRAFT,
                'currency' => $quote->rfq->buyer->profile->country === 'US' ? 'USD' : 'EUR',
                'amount' => $quote->price * $quote->quantity,
                'shipping_method' => $quote->shipping_details['method'] ?? 'standard',
                'shipping_details' => [
                    'method' => $quote->shipping_details['method'] ?? 'standard',
                    'terms' => $quote->shipping_details['terms'] ?? null,
                    'origin' => $quote->seller->profile->country,
                    'destination' => $quote->rfq->delivery_location,
                ],
                'payment_due_date' => now()->addDays(7),
                'estimated_delivery_date' => $quote->delivery_date,
            ]);

            $order->generateOrderNumber();
            $order->save();

            // Mark RFQ as closed
            $quote->rfq->update(['status' => 'closed']);
            
            // Mark other quotes as rejected
            $quote->rfq->quotes()
                ->where('id', '!=', $quote->id)
                ->update(['status' => 'rejected']);

            DB::commit();

            // Notify parties
            $quote->seller->notify(new OrderStatusUpdated($order));
            $quote->rfq->buyer->notify(new OrderStatusUpdated($order));

            return redirect()->route('orders.show', $order)
                ->with('success', 'Order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create order. Please try again.');
        }
    }

    public function update(Request $request, Order $order)
    {
        $this->authorize('update', $order);

        $validated = $request->validate([
            'shipping_method' => ['sometimes', 'required', 'string'],
            'shipping_details' => ['sometimes', 'required', 'array'],
            'estimated_delivery_date' => ['sometimes', 'required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $order->update($validated);

        if ($request->has('status')) {
            $order->updateStatus($request->status, $request->input('notes'));
        }

        return back()->with('success', 'Order updated successfully.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $order->updateStatus(Order::STATUS_CANCELLED, $validated['reason']);

        // Notify parties
        $order->buyer->notify(new OrderStatusUpdated($order, 'cancelled'));
        $order->seller->notify(new OrderStatusUpdated($order, 'cancelled'));

        return back()->with('success', 'Order cancelled successfully.');
    }
} 