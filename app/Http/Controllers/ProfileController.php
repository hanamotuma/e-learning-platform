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
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'state' => $user->state ?? '',
                'zip_code' => $user->zip_code ?? '',
                'country' => $user->country ?? '',
                'phone' => $user->phone ?? '',
                'bio' => $user->bio ?? '',
                'interests' => $user->interests ?? '',
                'education' => $user->education ?? '',
                'profile_picture_url' => $user->profile_picture_url,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'interests' => 'nullable|string|max:255',
            'education' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture_url) {
                $oldPath = str_replace('/storage/', '', $user->profile_picture_url);
                Storage::disk('public')->delete($oldPath);
            }
            
            // Store new profile picture
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $validated['profile_picture_url'] = '/storage/' . $path;
        }
        
        $user->update($validated);
        
        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}