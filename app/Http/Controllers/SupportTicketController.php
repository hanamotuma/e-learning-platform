<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportMessage;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    public function index()
    {
        $tickets = SupportTicket::with(['user'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
        ]);
    }
    
    public function adminIndex()
    {
        $tickets = SupportTicket::with(['user', 'messages.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
        ];
        
        return Inertia::render('Admin/Support/Index', [
            'tickets' => $tickets,
            'stats' => $stats,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);
        
        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'subject' => $request->subject,
            'priority' => $request->priority,
            'status' => 'open',
        ]);
        
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);
        
        // Notify admin
        $admins = \App\Models\User::role('admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'new_support_ticket',
                'title' => '🎫 New Support Ticket',
                'message' => Auth::user()->name . ' opened a new ticket: "' . $request->subject . '"',
                'action_url' => route('admin.support.show', $ticket->id),
            ]);
        }
        
        return redirect()->route('support.show', $ticket->id)
            ->with('success', 'Ticket created successfully. We\'ll respond shortly.');
    }
    
    public function show($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        return Inertia::render('Support/Show', [
            'ticket' => $ticket,
        ]);
    }
    
    public function adminShow($id)
    {
        $ticket = SupportTicket::with(['user', 'messages.user'])
            ->findOrFail($id);
        
        return Inertia::render('Admin/Support/Show', [
            'ticket' => $ticket,
        ]);
    }
    
    public function reply(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $request->validate([
            'message' => 'required|string',
        ]);
        
        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);
        
        $ticket->update(['status' => 'in_progress']);
        
        // Notify the other party
        $recipientId = $ticket->user_id == Auth::id() 
            ? ($ticket->assigned_to ?? $this->getAdminId()) 
            : $ticket->user_id;
        
        Notification::create([
            'user_id' => $recipientId,
            'type' => 'ticket_reply',
            'title' => '💬 New Reply on Your Ticket',
            'message' => Auth::user()->name . ' replied to ticket #' . $ticket->id . ': "' . $ticket->subject . '"',
            'action_url' => route('support.show', $ticket->id),
        ]);
        
        return redirect()->back()->with('success', 'Reply sent.');
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);
        
        $ticket = SupportTicket::findOrFail($id);
        $ticket->update(['status' => $request->status]);
        
        return response()->json(['success' => true]);
    }
    
    private function getAdminId()
    {
        $admin = \App\Models\User::role('admin')->first();
        return $admin ? $admin->id : 1;
    }
}