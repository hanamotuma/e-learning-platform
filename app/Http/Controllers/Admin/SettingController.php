<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Settings/Index');
    }

    public function getData()
    {
        $settings = Setting::all()->pluck('value', 'key')->toArray();

        // Default settings if not found
        $defaults = [
            'site_name' => 'LearnHub',
            'site_description' => 'Online Learning Platform',
            'contact_email' => 'admin@learnhub.com',
            'contact_phone' => '',
            'address' => '',
            'platform_commission' => 10,
            'instructor_commission' => 90,
            'tax_rate' => 0,
            'currency' => 'ETB',
            'currency_symbol' => 'ETB',
            'payment_methods' => ['telebirr', 'cbe_birr', 'card'],
            'chapa_secret_key' => '',
            'chapa_public_key' => '',
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'mail_from_address' => 'noreply@learnhub.com',
            'mail_from_name' => 'LearnHub',
            'require_email_verification' => true,
            'max_login_attempts' => 5,
            'session_timeout' => 120,
            'primary_color' => '#4f46e5',
            'logo_url' => '',
            'favicon_url' => '',
            'default_course_price' => 499,
            'max_quiz_attempts' => 3,
            'passing_score' => 70,
            'email_notifications' => true,
            'push_notifications' => true,
            'admin_notification_email' => ''
        ];

        $settings = array_merge($defaults, $settings);

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return response()->json(['success' => true]);
    }
}