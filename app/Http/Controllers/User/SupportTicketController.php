<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\Notification;
use App\Models\User;
use App\Events\NotificationSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    public function index()
    {
        return Inertia::render('User/Support/Index', [
            'tickets' => auth()->user()->tickets()->latest()->get()
        ]);
    }

    public function create()
    {
        return Inertia::render('User/Support/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,urgent',
            'message' => 'required|string',
        ]);

        return DB::transaction(function () use ($validated) {
            $ticket = SupportTicket::create([
                'subject' => $validated['subject'],
                'user_id' => auth()->id(),
                'priority' => $validated['priority'],
                'status' => 'open',
            ]);

            $ticket->messages()->create([
                'user_id' => auth()->id(),
                'message' => $validated['message'],
            ]);

            // Notify Admin
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                $notification = Notification::create([
                    'user_id' => $admin->id, 
                    'title' => 'New Support Ticket',
                    'message' => "New ticket from " . auth()->user()->full_name,
                    'type' => 'ticket',
                    'action_url' => route('admin.tickets.show', $ticket->id),
                    'is_read' => false,
                ]);
                broadcast(new NotificationSent($notification))->toOthers();
            }

            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Support ticket created successfully.');
        });
    }

    public function show(SupportTicket $ticket)
    {
        if (auth()->id() !== $ticket->user_id && auth()->user()->role !== 'admin') {
            abort(403);
        }

        return Inertia::render('User/Support/Show', [
            'ticket' => $ticket->load(['messages.user' => function($query) {
                $query->select('id', 'full_name', 'profile_picture_url');
            }])
        ]);
    }

    // ADD THIS: Allows user to reply to their own ticket
    public function storeMessage(Request $request, SupportTicket $ticket)
    {
        if (auth()->id() !== $ticket->user_id) {
            abort(403);
        }

        $request->validate(['message' => 'required|string']);

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        // Optional: Reset status to open if it was closed
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Message sent.');
    }
}