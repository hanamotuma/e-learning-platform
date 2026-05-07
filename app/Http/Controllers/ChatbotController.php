<?php

namespace App\Http\Controllers;

use App\Models\ChatbotSession;
use App\Models\ChatbotMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    public function index()
    {
        $session = ChatbotSession::where('user_id', Auth::id())
            ->where('status', 'active')
            ->latest()
            ->first();
        
        if (!$session) {
            $session = ChatbotSession::create([
                'user_id' => Auth::id(),
                'session_id' => Str::random(32),
                'status' => 'active',
            ]);
        }
        
        $messages = ChatbotMessage::where('chatbot_session_id', $session->id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        return Inertia::render('Chatbot/Index', [
            'session' => $session,
            'messages' => $messages,
        ]);
    }
    
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'session_id' => 'required|exists:chatbot_sessions,id',
        ]);
        
        $session = ChatbotSession::where('id', $request->session_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Save user message
        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'sender' => 'user',
            'message' => $request->message,
        ]);
        
        // Get conversation history for context
        $history = ChatbotMessage::where('chatbot_session_id', $session->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->reverse();
        
        // Build context
        $conversation = [];
        foreach ($history as $msg) {
            $conversation[] = [
                'role' => $msg->sender === 'user' ? 'user' : 'assistant',
                'content' => $msg->message,
            ];
        }
        
        // Add system prompt
        $systemPrompt = "You are LearnBot, a helpful assistant for LearnHub, an e-learning platform. ";
        $systemPrompt .= "You help students find courses, explain concepts, and provide learning guidance. ";
        $systemPrompt .= "Be friendly, concise, and educational. The user is a student learning online. ";
        $systemPrompt .= "Available course categories: Development, Design, Business, Marketing, AI & Data Science. ";
        $systemPrompt .= "If asked about enrollment, direct them to browse courses. If asked about technical issues, suggest contacting support.";
        
        // Call OpenAI API
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => array_merge(
                    [['role' => 'system', 'content' => $systemPrompt]],
                    $conversation,
                    [['role' => 'user', 'content' => $request->message]]
                ),
                'temperature' => 0.7,
                'max_tokens' => 500,
            ]);
            
            $data = $response->json();
            
            $botReply = $data['choices'][0]['message']['content'] ?? "I'm here to help! Could you please rephrase your question?";
            
        } catch (\Exception $e) {
            $botReply = "I'm having trouble connecting to my brain right now. Please try again in a moment, or contact our support team for immediate help.";
        }
        
        // Save bot response
        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'sender' => 'bot',
            'message' => $botReply,
        ]);
        
        // Update session conversation history
        $session->update([
            'conversation_history' => json_encode(array_merge(
                json_decode($session->conversation_history ?? '[]', true),
                [
                    ['role' => 'user', 'content' => $request->message],
                    ['role' => 'assistant', 'content' => $botReply],
                ]
            )),
        ]);
        
        return response()->json([
            'success' => true,
            'message' => $botReply,
            'message_id' => $session->messages->last()->id,
        ]);
    }
    
    public function endSession($id)
    {
        $session = ChatbotSession::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        $session->update(['status' => 'ended']);
        
        return redirect()->route('chatbot.index')->with('success', 'Chat session ended.');
    }
}