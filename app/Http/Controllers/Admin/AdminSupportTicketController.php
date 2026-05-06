<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSupportTicketController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Support/Index', [
            // Using simplePaginate or paginate for better performance
            'tickets' => SupportTicket::with('user')->latest()->paginate(10)
        ]);
    }

    public function show($id)
    {
        return Inertia::render('Admin/Support/Show', [
            'ticket' => SupportTicket::with(['user', 'messages.user'])->findOrFail($id)
        ]);
    }

    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $ticket = SupportTicket::findOrFail($id);

        $paths = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $paths[] = $file->store('support/attachments', 'public');
            }
        }

        $ticket->messages()->create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'attachments' => !empty($paths) ? json_encode($paths) : null,
        ]);

        // Update status to 'in_progress' so user sees admin is active
        $ticket->update(['status' => 'in_progress']);

        return back()->with('success', 'Response sent successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $ticket = SupportTicket::findOrFail($id);
        
        $request->validate([
            'status' => 'required|in:open,in_progress,closed'
        ]);

        $ticket->update(['status' => $request->status]);
        
        return back()->with('success', 'Status updated.');
    }
}