<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class StudentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('student')->withCount('enrollments');
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }
        
        if ($request->status === 'suspended') {
            $query->where('is_active', false);
        } elseif ($request->status === 'active') {
            $query->where('is_active', true);
        }
        
        $students = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'filters' => $request->only(['search', 'status']),
        ]);
    }
    
    public function show($id)
    {
        $student = User::role('student')->with(['enrollments.course', 'payments'])->findOrFail($id);
        
        $enrollments = $student->enrollments()->with('course')->latest()->get();
        $payments = $student->payments()->latest()->get();
        
        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
            'enrollments' => $enrollments,
            'payments' => $payments,
        ]);
    }
    
    public function toggleStatus($id)
    {
        $student = User::role('student')->findOrFail($id);
        $student->update(['is_active' => !$student->is_active]);
        
        $status = $student->is_active ? 'activated' : 'suspended';
        
        Notification::create([
            'user_id' => $student->id,
            'type' => 'account_status',
            'title' => "Account {$status}",
            'message' => "Your account has been {$status} by an administrator.",
            'action_url' => route('student.dashboard'),
        ]);
        
        return response()->json(['success' => true, 'is_active' => $student->is_active]);
    }
    
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);
        
        $student = User::role('student')->findOrFail($id);
        $student->update(['password' => Hash::make($request->password)]);
        
        Notification::create([
            'user_id' => $student->id,
            'type' => 'password_reset',
            'title' => 'Password Reset',
            'message' => 'Your password has been reset by an administrator.',
            'action_url' => route('profile.edit'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function issueRefund(Request $request, $id)
    {
        $request->validate([
            'payment_id' => 'required|exists:payments,id',
            'amount' => 'required|numeric|min:0',
            'reason' => 'required|string',
        ]);
        
        $payment = Payment::findOrFail($request->payment_id);
        $payment->update(['status' => 'refunded']);
        
        Notification::create([
            'user_id' => $id,
            'type' => 'refund_issued',
            'title' => 'Refund Issued',
            'message' => "A refund of {$request->amount} ETB has been issued for your purchase.",
            'action_url' => route('student.payments'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function verifyAccount($id)
    {
        $student = User::role('student')->findOrFail($id);
        $student->update(['email_verified_at' => now()]);
        
        return response()->json(['success' => true]);
    }
}