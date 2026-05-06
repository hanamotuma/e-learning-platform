<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use App\Models\ChatbotSession;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    /**
     * Display the Landing Page with Course Catalog and Chatbot context.
     */
    public function index()
    {
        // 1. Retrieve or generate the unique browser session ID
        $browserId = session()->getId();

        // 2. Find or create the chatbot session
        // This allows guests to start chatting before they even log in.
        $session = ChatbotSession::firstOrCreate(
            [
                'session_id' => $browserId, 
                'status' => 'active'
            ],
            [
                'user_id' => auth()->id()
            ]
        );

        // 3. Sync user_id if they were a guest but just authenticated
        if (auth()->check() && is_null($session->user_id)) {
            $session->update(['user_id' => auth()->id()]);
        }

        // 4. Fetch the Data for the Landing Page
        return Inertia::render('Home', [
            // Load messages so the chat history appears in the bubble
            'chatbotSession' => $session->load('messages'),
            
            // Get featured courses with their categories
            'courses' => Course::with('category')
                ->where('is_published', true) // Assuming you have a status column
                ->latest()
                ->take(8) 
                ->get(),

            // Get categories for the filter navigation
            'categories' => Category::all(),

            // Optional: Social Proof Stats for the Hero section
            'platformStats' => [
                'studentCount' => 1200, 
                'courseCount' => Course::count(),
                'rating' => 4.9
            ],

            // Pass authentication status explicitly for UI logic
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }
}