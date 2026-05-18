<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Announcements/Index');
    }

    public function getData()
    {
        $announcements = Announcement::orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => Announcement::count(),
            'published' => Announcement::where('status', 'published')->count(),
            'scheduled' => Announcement::where('status', 'scheduled')->count(),
            'expired' => Announcement::where('status', 'expired')->count()
        ];

        return response()->json([
            'announcements' => $announcements,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Announcements/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,important,update,maintenance',
            'target_audience' => 'required|in:all,students,instructors,both',
            'send_email' => 'boolean',
            'send_notification' => 'boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date'
        ]);

        $status = $request->publish_at && now()->lt($request->publish_at) ? 'scheduled' : 'published';

        $announcement = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'type' => $request->type,
            'target_audience' => $request->target_audience,
            'send_email' => $request->send_email,
            'send_notification' => $request->send_notification,
            'publish_at' => $request->publish_at,
            'expires_at' => $request->expires_at,
            'status' => $status
        ]);

        // Send notifications immediately if published
        if ($status === 'published') {
            $this->sendAnnouncementNotifications($announcement);
        }

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully');
    }

    public function edit($id)
    {
        $announcement = Announcement::findOrFail($id);
        return Inertia::render('Admin/Announcements/Edit', ['announcement' => $announcement]);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:general,important,update,maintenance',
            'target_audience' => 'required|in:all,students,instructors,both',
            'send_email' => 'boolean',
            'send_notification' => 'boolean',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date'
        ]);

        $announcement->update($request->all());

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully');
    }

    public function publish($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->update([
            'status' => 'published',
            'publish_at' => now()
        ]);

        $this->sendAnnouncementNotifications($announcement);

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['success' => true]);
    }

    private function sendAnnouncementNotifications($announcement)
    {
        $query = User::query();
        
        switch ($announcement->target_audience) {
            case 'students':
                $query->role('student');
                break;
            case 'instructors':
                $query->role('instructor');
                break;
            case 'both':
                $query->whereIn('role', ['student', 'instructor']);
                break;
            default:
                $query->all();
        }

        $users = $query->get();

        foreach ($users as $user) {
            if ($announcement->send_notification) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'announcement',
                    'title' => $announcement->title,
                    'message' => $announcement->content,
                    'action_url' => route('announcements.show', $announcement->id),
                    'is_read' => false
                ]);
            }

            if ($announcement->send_email) {
                // Send email logic here
                // Mail::to($user->email)->send(new AnnouncementMail($announcement));
            }
        }
    }
}