<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Enrollment;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // Public profile view
    public function publicProfile($id)
    {
        $user = User::findOrFail($id);
        
        $enrolledCourses = Enrollment::with(['course.instructor'])
            ->where('user_id', $id)
            ->where('status', 'active')
            ->get();
        
        $certificates = Certificate::with('course')
            ->where('user_id', $id)
            ->get();
        
        return Inertia::render('Profile/Public', [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'interests' => $user->interests,
                'education' => $user->education,
                'profile_picture_url' => $user->profile_picture_url,
                'created_at' => $user->created_at,
            ],
            'stats' => [
                'total_courses' => $enrolledCourses->count(),
                'completed_courses' => $enrolledCourses->filter(function($e) {
                    return $e->progress_percentage >= 100;
                })->count(),
                'certificates' => $certificates->count(),
                'average_progress' => $enrolledCourses->count() > 0 ? round($enrolledCourses->avg('progress_percentage')) : 0,
            ],
            'enrolledCourses' => $enrolledCourses,
            'certificates' => $certificates,
        ]);
    }
    
    // Edit profile form
    public function edit()
    {
        $user = Auth::user();
        
        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'bio' => $user->bio,
                'interests' => $user->interests,
                'education' => $user->education,
                'profile_picture_url' => $user->profile_picture_url,
            ]
        ]);
    }
    
    // Update profile
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'interests' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:5120',
        ]);
        
        $user->full_name = $validated['name'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->bio = $validated['bio'] ?? null;
        $user->interests = $validated['interests'] ?? null;
        $user->education = $validated['education'] ?? null;
        
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture_url) {
                $oldPath = str_replace('/storage/', '', $user->profile_picture_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture_url = '/storage/' . $path;
        }
        
        $user->save();
        
        return redirect()->route('profile.public', $user->id)->with('success', 'Profile updated successfully!');
    }
}