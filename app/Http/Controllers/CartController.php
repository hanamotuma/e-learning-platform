<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $total = collect($cart)->sum('price');
        
        return response()->json([
            'cart' => array_values($cart),
            'total' => $total,
            'count' => count($cart)
        ]);
    }
    
    public function add(Course $course)
    {
        $cart = session()->get('cart', []);
        
        if (!isset($cart[$course->id])) {
            $cart[$course->id] = [
                'id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
                'original_price' => $course->original_price,
                'image' => $course->image,
                'instructor' => $course->instructor,
                'badge' => $course->badge,
                'rating' => $course->rating,
                'hours' => $course->hours,
                'level' => $course->level
            ];
            session()->put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Course added to cart',
                'cart_count' => count($cart)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Course already in cart'
        ]);
    }
    
    public function remove(Course $course)
    {
        $cart = session()->get('cart', []);
        
        if (isset($cart[$course->id])) {
            unset($cart[$course->id]);
            session()->put('cart', $cart);
            
            return response()->json([
                'success' => true,
                'message' => 'Course removed from cart',
                'cart_count' => count($cart)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Course not found in cart'
        ]);
    }
    
    public function count()
    {
        $cart = session()->get('cart', []);
        return response()->json(['count' => count($cart)]);
    }
}