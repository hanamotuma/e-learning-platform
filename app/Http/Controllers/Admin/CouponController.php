<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CouponController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Coupons/Index');
    }

    public function getData()
    {
        $coupons = Coupon::orderBy('created_at', 'desc')->get();

        $stats = [
            'total' => Coupon::count(),
            'active' => Coupon::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })->count(),
            'expired' => Coupon::where('expires_at', '<', now())->count(),
            'total_used' => Coupon::sum('used_count')
        ];

        return response()->json([
            'coupons' => $coupons,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Coupons/Create');
    }

    public function store(Request $request)
{
    $request->validate([
        'code' => 'required|string|unique:coupons,code',
        'type' => 'required|in:percentage,fixed',
        'value' => 'required|numeric|min:0',
        'max_uses' => 'nullable|integer|min:1',
        'min_purchase' => 'nullable|numeric|min:0',
        'starts_at' => 'nullable|date',
        'expires_at' => 'nullable|date|after:starts_at',
        'is_active' => 'boolean'
    ]);

    $coupon = Coupon::create([
        'code' => strtoupper($request->code),
        'type' => $request->type,
        'value' => $request->value,
        'max_uses' => $request->max_uses,
        'min_purchase' => $request->min_purchase ?? 0,
        'starts_at' => $request->starts_at,
        'expires_at' => $request->expires_at,
        'is_active' => $request->is_active ?? true,
        'used_count' => 0
    ]);

    return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully');
}

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return Inertia::render('Admin/Coupons/Edit', ['coupon' => $coupon]);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $id,
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'min_purchase' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'is_active' => 'boolean'
        ]);

        $coupon->update($request->all());

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully');
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['success' => true]);
    }
}