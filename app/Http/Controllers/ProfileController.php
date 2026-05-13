<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        
        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'bio' => $user->bio ?? '',
                'interests' => $user->interests ?? '',
                'education' => $user->education ?? '',
                'profile_picture_url' => $user->profile_picture_url,
            ]
        ]);
    }

    public function publicProfile($id)
{
    $user = \App\Models\User::findOrFail($id);
    
    $enrolledCourses = \App\Models\Enrollment::with(['course.instructor'])
        ->where('user_id', $id)
        ->orderBy('created_at', 'desc')
        ->get();
    
    $certificates = \App\Models\Certificate::with(['course'])
        ->where('user_id', $id)
        ->orderBy('created_at', 'desc')
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
            'created_at' => $user->created_at,
            'profile_picture_url' => $user->profile_picture_url,
        ],
        'enrolledCourses' => $enrolledCourses,
        'certificates' => $certificates,
    ]);
}

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id . ',user_id',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id . ',user_id',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'interests' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        // Update user data
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
        
        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}