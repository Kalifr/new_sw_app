<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportCategory;
use App\Models\SupportFaq;
use App\Models\User;
use App\Notifications\NewSupportTicketNotification;
use App\Notifications\SupportTicketUpdatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportTicket::query()
            ->with(['category', 'user', 'assignedTo'])
            ->latest();

        if ($request->user()->hasRole('customer')) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->paginate(10)
            ->through(fn ($ticket) => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category->name,
                'created_at' => $ticket->created_at->diffForHumans(),
                'last_response_at' => $ticket->last_response_at?->diffForHumans(),
                'user' => $ticket->user->only(['id', 'name']),
                'assigned_to' => $ticket->assignedTo?->only(['id', 'name']),
            ]);

        $categories = SupportCategory::active()->ordered()->get();

        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'filters' => $request->only(['status', 'category', 'priority']),
        ]);
    }

    public function create()
    {
        $categories = SupportCategory::active()->ordered()->get();
        $faqs = SupportFaq::published()->latest()->take(5)->get();

        return Inertia::render('Support/Create', [
            'categories' => $categories,
            'suggested_faqs' => $faqs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:support_categories,id',
            'priority' => 'required|in:low,medium,high',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $ticket->attachments()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $file->store('support-attachments'),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Notify support staff
        $supportStaff = User::role('support')->get();
        Notification::send($supportStaff, new NewSupportTicketNotification($ticket));

        return redirect()->route('support.show', $ticket)
            ->with('success', 'Support ticket created successfully.');
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category',
            'user',
            'assignedTo',
            'responses' => fn($query) => $query->with('user', 'attachments')->latest(),
            'attachments',
        ]);

        return Inertia::render('Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'description' => $ticket->description,
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'category' => $ticket->category,
                'created_at' => $ticket->created_at->format('Y-m-d H:i:s'),
                'user' => $ticket->user->only(['id', 'name', 'email']),
                'assigned_to' => $ticket->assignedTo?->only(['id', 'name']),
                'responses' => $ticket->responses->map(fn($response) => [
                    'id' => $response->id,
                    'content' => $response->content,
                    'created_at' => $response->created_at->diffForHumans(),
                    'user' => $response->user->only(['id', 'name']),
                    'attachments' => $response->attachments->map(fn($attachment) => [
                        'id' => $attachment->id,
                        'file_name' => $attachment->file_name,
                        'download_url' => $attachment->getDownloadUrl(),
                        'preview_url' => $attachment->getPreviewUrl(),
                    ]),
                ]),
                'attachments' => $ticket->attachments->map(fn($attachment) => [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'download_url' => $attachment->getDownloadUrl(),
                    'preview_url' => $attachment->getPreviewUrl(),
                ]),
            ],
        ]);
    }

    public function update(Request $request, SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'status' => 'sometimes|required|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|required|in:low,medium,high',
            'assigned_to_id' => 'sometimes|nullable|exists:users,id',
        ]);

        $ticket->update($validated);

        // Notify relevant users about the update
        $notifyUsers = collect([$ticket->user]);
        if ($ticket->assignedTo) {
            $notifyUsers->push($ticket->assignedTo);
        }
        
        Notification::send($notifyUsers, new SupportTicketUpdatedNotification($ticket));

        return back()->with('success', 'Support ticket updated successfully.');
    }

    public function close(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'closed']);
        
        // Request feedback if it hasn't been provided
        if (!$ticket->feedback()->exists()) {
            // TODO: Send feedback request notification
        }

        return back()->with('success', 'Support ticket closed successfully.');
    }

    public function reopen(SupportTicket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update(['status' => 'open']);

        return back()->with('success', 'Support ticket reopened successfully.');
    }

    public function faq()
    {
        $faqs = SupportFaq::with('category')
            ->published()
            ->orderBy('helpful_count', 'desc')
            ->get()
            ->groupBy('category.name');

        $categories = SupportCategory::active()
            ->ordered()
            ->get();

        return Inertia::render('Support/Faq', [
            'faqs' => $faqs,
            'categories' => $categories,
        ]);
    }

    public function markFaqHelpful(SupportFaq $faq)
    {
        $faq->incrementHelpfulCount();
        return response()->json(['success' => true]);
    }
} 