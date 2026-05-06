<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'chapa_public_key' => config('services.chapa.public_key'),
                'gemini_api_key' => config('services.gemini.key'),
                'mail_from' => config('mail.from.address'),
            ]
        ]);
    }

    public function update(Request $request)
    {
        // NOTE: In a standard setup, you cannot write to .env at runtime easily.
        // For a "No Model" approach, we usually flash a message or 
        // instructions on how to update the environment file.
        
        return back()->with('message', 'To update these permanently, please modify the system .env file.');
    }
}