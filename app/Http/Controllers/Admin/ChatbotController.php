<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotSession;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class ChatbotController extends Controller
{
    public function sendMessage(Request $request)
    {
        try {
            $request->validate(['message' => 'required|string|max:2000']);
            $userMessage = $request->message;

            // 1. Try Local Logic First (Fast & Saves API Costs)
            $botResponse = $this->generateLocalResponse(strtolower($userMessage));

            // 2. If Local Logic hits the Fallback, ask Gemini instead
            if (str_contains($botResponse, "I'm not quite sure")) {
                $botResponse = $this->askGemini($userMessage);
            }

            // 3. Save History
            $laravelSessionId = session()->getId();
            $session = ChatbotSession::firstOrCreate(
                ['session_id' => $laravelSessionId],
                ['user_id' => auth()->id(), 'status' => 'active']
            );

            $session->messages()->create(['sender' => 'user', 'message' => $userMessage]);
            $session->messages()->create(['sender' => 'bot', 'message' => $botResponse]);

            return response()->json(['message' => $botResponse]);

        } catch (\Exception $e) {
            Log::error("Chatbot Error: " . $e->getMessage());
            return response()->json([
                'message' => "I'm having a bit of a brain freeze. Can you try that again?"
            ], 200);
        }
    }

    /**
     * Connect to Google Gemini API
     */
    private function askGemini(string $prompt): string
{
    $apiKey = env('GEMINI_API_KEY');
    
    // Exact URL that works in 2026
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . $apiKey;
    $response = Http::withoutVerifying() // <--- ADD THIS LINE TO BYPASS SSL ERRORS
        ->withHeaders(['Content-Type' => 'application/json'])
        ->post($url, [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ]);

    if ($response->successful()) {
        $data = $response->json();
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? "Thinking...";
    }

    // If it still fails, this will show the REAL error on your screen
    return "Google Error: " . $response->status() . " - " . $response->body();
}
    private function generateLocalResponse(string $message): string
    {
        $knowledge = [
            'hello' => "Hello! Welcome to our E-Learning platform. How can I help you today?",
            'payment' => "We support Telebirr, CBE Birr, and Credit Cards via Chapa.",
            'certificate' => "You get a PDF certificate automatically once you pass the final quiz.",
        ];

        foreach ($knowledge as $key => $answer) {
            if (str_contains($message, $key)) return $answer;
        }

        $courses = Course::where('title', 'LIKE', "%{$message}%")->get();
        if ($courses->isNotEmpty()) {
            $response = "I found these courses for you:\n\n";
            foreach ($courses as $course) {
                $response .= "🎓 {$course->title} - {$course->price} ETB\n";
            }
            return $response;
        }

        return "I'm not quite sure about that."; 
    }
}