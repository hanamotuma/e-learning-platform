<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PaymentManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['user', 'course']);
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_id', 'like', "%{$request->search}%")
                  ->orWhereHas('user', function($u) use ($request) {
                      $u->where('email', 'like', "%{$request->search}%");
                  });
            });
        }
        
        $payments = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'platform_commission' => Payment::where('status', 'completed')->sum('amount') * 0.1,
            'instructor_payout' => Payment::where('status', 'completed')->sum('amount') * 0.9,
            'pending_withdrawals' => Withdrawal::where('status', 'pending')->sum('amount'),
        ];
        
        return Inertia::render('Admin/Payments/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search']),
        ]);
    }
    
    public function show($id)
    {
        $payment = Payment::with(['user', 'course'])->findOrFail($id);
        
        return Inertia::render('Admin/Payments/Show', [
            'payment' => $payment,
        ]);
    }
    
    public function refund(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);
        
        $payment = Payment::findOrFail($id);
        $payment->update([
            'status' => 'refunded',
            'refund_reason' => $request->reason,
            'refunded_at' => now(),
        ]);
        
        // Update enrollment status
        if ($payment->enrollment_id) {
            $payment->enrollment->update(['status' => 'refunded']);
        }
        
        return response()->json(['success' => true]);
    }
    
    public function withdrawals(Request $request)
    {
        $query = Withdrawal::with(['user']);
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $stats = [
            'total_requested' => Withdrawal::where('status', 'pending')->sum('amount'),
            'total_paid' => Withdrawal::where('status', 'paid')->sum('amount'),
            'pending_count' => Withdrawal::where('status', 'pending')->count(),
        ];
        
        return Inertia::render('Admin/Withdrawals/Index', [
            'withdrawals' => $withdrawals,
            'stats' => $stats,
            'filters' => $request->only(['status']),
        ]);
    }
    
    public function approveWithdrawal($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update([
            'status' => 'approved',
            'approved_at' => now(),
        ]);
        
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'type' => 'withdrawal_approved',
            'title' => 'Withdrawal Approved',
            'message' => "Your withdrawal request of {$withdrawal->amount} ETB has been approved. Funds will be sent shortly.",
            'action_url' => route('instructor.earnings'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function markWithdrawalPaid($id)
    {
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'type' => 'withdrawal_paid',
            'title' => 'Withdrawal Completed',
            'message' => "Your withdrawal of {$withdrawal->amount} ETB has been sent to your account.",
            'action_url' => route('instructor.earnings'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function rejectWithdrawal(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string',
        ]);
        
        $withdrawal = Withdrawal::findOrFail($id);
        $withdrawal->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);
        
        Notification::create([
            'user_id' => $withdrawal->user_id,
            'type' => 'withdrawal_rejected',
            'title' => 'Withdrawal Rejected',
            'message' => "Your withdrawal request was rejected. Reason: {$request->reason}",
            'action_url' => route('instructor.earnings'),
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function export(Request $request)
    {
        $payments = Payment::with(['user', 'course'])
            ->whereBetween('created_at', [$request->start_date, $request->end_date])
            ->get();
        
        $filename = 'payments_export_' . Carbon::now()->format('Y-m-d') . '.csv';
        
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Date', 'Transaction ID', 'Student', 'Course', 'Amount', 'Commission', 'Instructor Earnings', 'Status']);
        
        foreach ($payments as $payment) {
            fputcsv($handle, [
                $payment->created_at->format('Y-m-d'),
                $payment->transaction_id,
                $payment->user->email,
                $payment->course->title,
                $payment->amount,
                $payment->amount * 0.1,
                $payment->amount * 0.9,
                $payment->status,
            ]);
        }
        
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        return response($csv, 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$filename}");
    }
}