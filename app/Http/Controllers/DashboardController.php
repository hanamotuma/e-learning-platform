<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
// Import your models here (make sure these exist!)
// use App\Models\CourseEnrollment; 

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Home', [
            'user' => $request->user(),
            'enrolledCourses' => [], // Empty array for now to prevent errors
            'certificates' => [],
            'wishlistCount' => 0
        ]);
    }
}