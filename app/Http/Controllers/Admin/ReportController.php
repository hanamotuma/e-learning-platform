<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller; // Add this to extend the base controller

use App\Models\Report;
use App\Jobs\GenerateReportJob;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Report/Index', [
        'reports' => Report::with('generator')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
    ]);
    }

    public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'report_type' => 'required|in:course,progress,payment',
    ]);

    $report = \App\Models\Report::create([
        'user_id' => auth()->id(),
        'title' => $request->title,
        'report_type' => $request->report_type,
        'status' => 'pending',
    ]);

    // Use dispatchSync to run the code IMMEDIATELY
    \App\Jobs\GenerateReportJob::dispatchSync($report);

    return back()->with('success', 'Report generated successfully!');
}

    public function download(\App\Models\Report $report)
{
    // Check if file exists
    if (!$report->file_path || !\Storage::disk('public')->exists($report->file_path)) {
        abort(404, 'File not found on server.');
    }

    // Return the download response
    return \Storage::disk('public')->download($report->file_path);
}
}