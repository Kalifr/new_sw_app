<?php

namespace App\Http\Controllers;

use App\Events\NewMessage;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MessageController extends Controller
{
    public function index()
    {
        $threads = auth()->user()->messageThreads()
            ->with(['participants', 'latestMessage'])
            ->withCount(['messages'])
            ->paginate(20);

        return Inertia::render('Messages/Index', [
            'threads' => $threads,
        ]);
    }

    public function show(MessageThread $thread)
    {
        $this->authorize('view', $thread);

        $messages = $thread->messages()
            ->with(['user', 'status'])
            ->latest()
            ->paginate(50);

        $thread->markAsReadForUser(auth()->user());

        return Inertia::render('Messages/Show', [
            'thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'participants' => $thread->participants,
            ],
            'messages' => $messages,
        ]);
    }

    public function store(Request $request, MessageThread $thread)
    {
        $this->authorize('participate', $thread);

        $validated = $request->validate([
            'body' => ['required', 'string'],
            'type' => ['required', 'in:text,image,file'],
            'file' => ['required_if:type,image,file', 'file', 'max:10240'],
        ]);

        $metadata = [];

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('message-attachments', 'public');
            $metadata = [
                'filename' => $request->file('file')->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $request->file('file')->getMimeType(),
                'size' => $request->file('file')->getSize(),
            ];
        }

        $message = $thread->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['body'],
            'type' => $validated['type'],
            'metadata' => $metadata,
        ]);

        broadcast(new NewMessage($message))->toOthers();

        return back();
    }

    public function createThread(Request $request)
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*' => ['exists:users,id'],
            'message' => ['required', 'string'],
        ]);

        $thread = MessageThread::create([
            'subject' => $validated['subject'],
        ]);

        // Add participants including the current user
        $participants = collect($validated['participants'])
            ->push(auth()->id())
            ->unique()
            ->values();

        $thread->participants()->attach($participants);

        // Create the first message
        $message = $thread->messages()->create([
            'user_id' => auth()->id(),
            'body' => $validated['message'],
            'type' => 'text',
        ]);

        broadcast(new NewMessage($message))->toOthers();

        return redirect()->route('messages.show', $thread);
    }

    public function markAsRead(Message $message)
    {
        $this->authorize('view', $message->thread);

        $message->markAsReadForUser(auth()->user());
        $message->thread->markAsReadForUser(auth()->user());

        return response()->json(['success' => true]);
    }

    public function processEmailReply(Request $request)
    {
        $token = decrypt($request->header('X-PM-Reply-Token'));

        if ($token['expires'] < now()->timestamp) {
            abort(403, 'Reply token has expired');
        }

        $thread = MessageThread::findOrFail($token['thread_id']);
        $user = User::findOrFail($token['user_id']);

        $message = $thread->messages()->create([
            'user_id' => $user->id,
            'body' => $request->input('body'),
            'type' => 'text',
            'email_message_id' => $request->header('Message-ID'),
        ]);

        broadcast(new NewMessage($message));

        return response()->json(['success' => true]);
    }
} 