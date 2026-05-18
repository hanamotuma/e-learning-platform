<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CategoryManagementController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('courses')
            ->orderBy('name')
            ->get();
        
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
        ]);
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);
        
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'icon' => $request->icon,
        ]);
        
        return redirect()->back()->with('success', 'Category created successfully');
    }
    
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);
        
        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'icon' => $request->icon,
        ]);
        
        return redirect()->back()->with('success', 'Category updated successfully');
    }
    
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        if ($category->courses()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with associated courses');
        }
        
        $category->delete();
        
        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}