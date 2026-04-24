<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\Lesson;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    // STORE RESOURCE (UPLOAD FILE)
    public function store(Request $request, Lesson $lesson)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');

        $path = $file->store('resources', 'public');

        $lesson->resources()->create([
            'title' => $request->title,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(), // 🔥 REQUIRED FIX
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'download_count' => 0,
            'is_public' => true,
            'is_downloadable' => true,
        ]);

        return back();
    }

    // DELETE RESOURCE
    public function destroy(Resource $resource)
    {
        $resource->delete();
        return back();
    }
}