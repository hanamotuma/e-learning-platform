<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    /**
     * Display a listing of notifications
     */
    public function index()
    {
        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => Notification::where('user_id', auth()->id())
                ->latest()
                ->paginate(15)
        ]);
    }

    /**
     * Mark a single notification as read
     */
    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', auth()->id())->findOrFail($id);
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return back();
    }

    /**
     * Mark all notifications as read for current admin
     */
    public function markAllAsRead()
    {
        Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Remove a notification
     */
    public function destroy($id)
    {
        Notification::where('user_id', auth()->id())->findOrFail($id)->delete();
        return back();
    }
}