<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\InspectionRecord;
use App\Notifications\InspectionRecordUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InspectionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('view-inspections');

        $query = InspectionRecord::query()
            ->with(['order.rfq.product', 'inspector:id,name'])
            ->when($request->user()->isInspector(), function ($query) use ($request) {
                $query->where('inspector_id', $request->user()->id);
            })
            ->when($request->input('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest();

        return Inertia::render('Inspections/Index', [
            'inspections' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only(['status']),
            'statuses' => [
                InspectionRecord::STATUS_PENDING => 'Pending',
                InspectionRecord::STATUS_IN_PROGRESS => 'In Progress',
                InspectionRecord::STATUS_PASSED => 'Passed',
                InspectionRecord::STATUS_FAILED => 'Failed',
                InspectionRecord::STATUS_NEEDS_REVIEW => 'Needs Review',
            ],
        ]);
    }

    public function show(InspectionRecord $inspection): Response
    {
        $this->authorize('view', $inspection);

        $inspection->load([
            'order.rfq.product',
            'inspector:id,name',
        ]);

        return Inertia::render('Inspections/Show', [
            'inspection' => $inspection,
            'canEdit' => $inspection->isPending() || $inspection->isInProgress(),
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $this->authorize('create-inspection', $order);

        $validated = $request->validate([
            'findings' => ['required', 'string'],
            'checklist_results' => ['required', 'array'],
            'checklist_results.*.item' => ['required', 'string'],
            'checklist_results.*.status' => ['required', 'in:passed,failed,skipped'],
            'checklist_results.*.notes' => ['nullable', 'string'],
            'location' => ['required', 'string'],
            'photos.*' => ['sometimes', 'file', 'image', 'max:10240'],
        ]);

        $inspection = $order->inspectionRecords()->create([
            'inspector_id' => $request->user()->id,
            'status' => InspectionRecord::STATUS_IN_PROGRESS,
            'findings' => $validated['findings'],
            'checklist_results' => $validated['checklist_results'],
            'location' => $validated['location'],
            'inspection_date' => now(),
        ]);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inspection-photos');
                $inspection->addPhoto($path, $photo->getClientOriginalName());
            }
        }

        // Update order status
        $order->updateStatus(Order::STATUS_INSPECTION_PENDING);

        // Notify parties
        $order->buyer->notify(new InspectionRecordUpdated($inspection, 'created'));
        $order->seller->notify(new InspectionRecordUpdated($inspection, 'created'));

        return redirect()->route('inspections.show', $inspection)
            ->with('success', 'Inspection record created successfully.');
    }

    public function update(Request $request, InspectionRecord $inspection)
    {
        $this->authorize('update', $inspection);

        $validated = $request->validate([
            'findings' => ['sometimes', 'required', 'string'],
            'checklist_results' => ['sometimes', 'required', 'array'],
            'checklist_results.*.item' => ['required', 'string'],
            'checklist_results.*.status' => ['required', 'in:passed,failed,skipped'],
            'checklist_results.*.notes' => ['nullable', 'string'],
            'photos.*' => ['sometimes', 'file', 'image', 'max:10240'],
            'status' => ['sometimes', 'required', 'in:in_progress,passed,failed,needs_review'],
        ]);

        $inspection->update($validated);

        // Handle photo uploads
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('inspection-photos');
                $inspection->addPhoto($path, $photo->getClientOriginalName());
            }
        }

        // Update checklist if provided
        if (isset($validated['checklist_results'])) {
            $inspection->updateChecklist($validated['checklist_results']);
        }

        // Notify parties
        $inspection->order->buyer->notify(new InspectionRecordUpdated($inspection, 'updated'));
        $inspection->order->seller->notify(new InspectionRecordUpdated($inspection, 'updated'));

        return back()->with('success', 'Inspection record updated successfully.');
    }

    public function complete(Request $request, InspectionRecord $inspection)
    {
        $this->authorize('complete', $inspection);

        $validated = $request->validate([
            'status' => ['required', 'in:passed,failed'],
            'notes' => ['required', 'string'],
        ]);

        $inspection->update([
            'status' => $validated['status'],
            'findings' => $inspection->findings . "\n\nFinal Notes: " . $validated['notes'],
        ]);

        // Update order status
        if ($validated['status'] === 'passed') {
            $inspection->order->updateStatus(Order::STATUS_INSPECTION_PASSED);
        }

        // Notify parties
        $inspection->order->buyer->notify(new InspectionRecordUpdated($inspection, 'completed'));
        $inspection->order->seller->notify(new InspectionRecordUpdated($inspection, 'completed'));

        return back()->with('success', 'Inspection completed successfully.');
    }

    public function downloadPhoto(InspectionRecord $inspection, string $photoPath)
    {
        $this->authorize('view', $inspection);

        if (!Storage::exists($photoPath)) {
            abort(404, 'Photo not found.');
        }

        return Storage::download($photoPath);
    }
} 