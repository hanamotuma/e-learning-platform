<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Support/Index');
    }

    public function getData()
    {
        $tickets = SupportTicket::with(['user', 'messages'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'subject' => $ticket->subject,
                    'priority' => $ticket->priority,
                    'status' => $ticket->status,
                    'created_at' => $ticket->created_at,
                    'user' => $ticket->user ? [
                        'id' => $ticket->user->id,
                        'name' => $ticket->user->full_name ?? $ticket->user->name,
                        'email' => $ticket->user->email
                    ] : null,
                    'messages_count' => $ticket->messages->count()
                ];
            });

        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'urgent' => SupportTicket::where('priority', 'urgent')->where('status', '!=', 'resolved')->count()
        ];

        return response()->json([
            'tickets' => $tickets,
            'stats' => $stats
        ]);
    }

    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])
            ->findOrFail($id);
        
        return Inertia::render('Admin/Support/Show', [
            'ticket' => [
                'id' => $ticket->id,
                'subject' => $ticket->subject,
                'priority' => $ticket->priority,
                'status' => $ticket->status,
                'created_at' => $ticket->created_at,
                'user' => $ticket->user,
                'messages' => $ticket->messages
            ]
        ]);
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $ticket = SupportTicket::findOrFail($id);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        $ticket->update(['status' => 'in_progress']);

        // Notify user
        Notification::create([
            'user_id' => $ticket->user_id,
            'type' => 'ticket_reply',
            'title' => '💬 Support Ticket Updated',
            'message' => 'An admin has responded to your ticket: ' . $ticket->subject,
            'action_url' => route('support.show', $ticket->id),
            'is_read' => false
        ]);

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed'
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }
}