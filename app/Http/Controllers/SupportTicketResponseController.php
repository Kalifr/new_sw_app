<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketResponse;
use App\Notifications\NewTicketResponseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class SupportTicketResponseController extends Controller
{
    public function store(Request $request, SupportTicket $ticket)
    {
        $this->authorize('respond', $ticket);

        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $response = $ticket->responses()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'is_internal' => $validated['is_internal'] ?? false,
            'response_type' => 'reply',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $response->attachments()->create([
                    'ticket_id' => $ticket->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $file->store('support-attachments'),
                    'mime_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Update ticket status if needed
        if ($request->user()->hasRole('support') && $ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        // Update last response timestamp
        $ticket->update(['last_response_at' => now()]);

        // Notify relevant users
        $notifyUsers = collect();
        
        if ($validated['is_internal'] ?? false) {
            // For internal notes, notify only support staff
            $notifyUsers = $ticket->getAssignedSupportStaff();
        } else {
            // For regular responses, notify the other party
            if ($request->user()->id === $ticket->user_id) {
                $notifyUsers = $ticket->getAssignedSupportStaff();
            } else {
                $notifyUsers->push($ticket->user);
            }
        }

        Notification::send($notifyUsers, new NewTicketResponseNotification($ticket, $response));

        return back()->with('success', 'Response added successfully.');
    }

    public function update(Request $request, SupportTicket $ticket, SupportTicketResponse $response)
    {
        $this->authorize('update', $response);

        $validated = $request->validate([
            'content' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $response->update($validated);

        return back()->with('success', 'Response updated successfully.');
    }

    public function destroy(SupportTicket $ticket, SupportTicketResponse $response)
    {
        $this->authorize('delete', $response);

        $response->delete();

        return back()->with('success', 'Response deleted successfully.');
    }

    public function downloadAttachment(SupportTicket $ticket, SupportTicketResponse $response, $attachmentId)
    {
        $attachment = $response->attachments()->findOrFail($attachmentId);
        
        $this->authorize('view', $ticket);

        return response()->download(
            storage_path('app/' . $attachment->file_path),
            $attachment->file_name
        );
    }

    public function deleteAttachment(SupportTicket $ticket, SupportTicketResponse $response, $attachmentId)
    {
        $attachment = $response->attachments()->findOrFail($attachmentId);
        
        $this->authorize('delete', $response);

        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
} 