<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseApiController extends Controller
{
    public function featured()
    {
        $courses = Course::latest()->take(6)->get();

        return response()->json($courses);
    }
}