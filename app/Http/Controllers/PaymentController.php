<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentRecord;
use App\Notifications\PaymentRecordUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request, Order $order)
    {
        $this->authorize('record-payment', $order);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transaction_id' => ['required', 'string', 'max:255'],
            'receipt' => ['required', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Store payment receipt
        $receiptPath = $request->file('receipt')->store('payment-receipts');

        // Record payment
        $payment = $order->recordPayment(
            $validated['amount'],
            $order->currency,
            [
                'transaction_id' => $validated['transaction_id'],
                'receipt_path' => $receiptPath,
                'notes' => $validated['notes'],
            ]
        );

        // Create payment receipt document
        $order->documents()->create([
            'type' => 'payment_receipt',
            'file_path' => $receiptPath,
            'file_name' => $request->file('receipt')->getClientOriginalName(),
            'mime_type' => $request->file('receipt')->getMimeType(),
            'file_size' => $request->file('receipt')->getSize(),
            'metadata' => [
                'payment_id' => $payment->id,
                'amount' => $validated['amount'],
                'currency' => $order->currency,
                'transaction_id' => $validated['transaction_id'],
            ],
        ]);

        // Notify seller about payment
        $order->seller->notify(new PaymentRecordUpdated($payment, 'recorded'));

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function verify(Request $request, PaymentRecord $payment)
    {
        $this->authorize('verify-payment', $payment->order);

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment->verify($request->user(), $validated['notes']);

        // Update order status if fully paid
        if ($payment->order->payment_status === Order::PAYMENT_STATUS_PAID) {
            $payment->order->updateStatus(Order::STATUS_PAYMENT_VERIFIED);
        }

        // Notify buyer
        $payment->order->buyer->notify(new PaymentRecordUpdated($payment, 'verified'));

        return back()->with('success', 'Payment verified successfully.');
    }

    public function reject(Request $request, PaymentRecord $payment)
    {
        $this->authorize('verify-payment', $payment->order);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $payment->reject($request->user(), $validated['reason']);

        // Notify buyer
        $payment->order->buyer->notify(new PaymentRecordUpdated($payment, 'rejected'));

        return back()->with('success', 'Payment rejected.');
    }

    public function downloadReceipt(PaymentRecord $payment)
    {
        $this->authorize('view', $payment->order);

        $receiptPath = $payment->metadata['receipt_path'] ?? null;

        if (!$receiptPath || !Storage::exists($receiptPath)) {
            throw ValidationException::withMessages([
                'receipt' => ['Payment receipt not found.'],
            ]);
        }

        return Storage::download($receiptPath, 'payment_receipt.' . pathinfo($receiptPath, PATHINFO_EXTENSION));
    }
} 