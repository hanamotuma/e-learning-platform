<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CourseEnrollment;
use App\Models\Certificate;
use App\Models\Wishlist;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Fetch enrolled courses with course details and progress
        $enrolledCourses = CourseEnrollment::where('user_id', $user->id)
            ->with('course') // Assumes a 'course' relationship in CourseEnrollment model
            ->get();

        // 2. Fetch user certificates
        $certificates = Certificate::where('user_id', $user->id)->get();

        // 3. Count wishlist items
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();

        return Inertia::render('student/Dashboard', [
            'user' => $user,
            'enrolledCourses' => $enrolledCourses,
            'certificates' => $certificates,
            'wishlistCount' => $wishlistCount,
        ]);
    }
}