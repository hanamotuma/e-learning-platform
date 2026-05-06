<?php

namespace App\Jobs;

use App\Models\Report;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Report $report) {}

   // Inside app/Jobs/GenerateReportJob.php -> handle() method

public function handle(): void
{
    $this->report->update(['status' => 'processing']);

    $csvData = "";
    
    if ($this->report->report_type === 'payment') {
        // FETCH THE DETAILED DATA WE DISCUSSED
        $enrollments = \App\Models\Enrollment::with(['user', 'course', 'payment'])->get();
        $csvData = "Enrollment_ID,Student_Name,Course_Title,Amount,Status,Transaction_Ref,Paid_At\n";
        
        foreach ($enrollments as $e) {
            $csvData .= sprintf(
                "%s,\"%s\",\"%s\",%s,\"%s\",\"%s\",\"%s\"\n",
                $e->id,
                $e->user->name ?? 'N/A',
                $e->course->title ?? 'N/A',
                $e->payment->amount ?? 0,
                $e->status,
                $e->payment->transaction_reference ?? 'N/A',
                $e->payment->paid_at ?? $e->created_at
            );
        }
    } else {
        // Default Course Report logic (like your report_6.csv)
        $courses = \App\Models\Course::all();
        $csvData = "ID,Course Title,Status,Created At\n";
        foreach ($courses as $c) {
            $csvData .= "{$c->id},\"{$c->title}\",{$c->status},{$c->created_at}\n";
        }
    }

    $fileName = 'reports/report_' . $this->report->id . '.csv';
    \Storage::disk('public')->put($fileName, $csvData);

    $this->report->update([
        'status' => 'completed',
        'file_path' => $fileName
    ]);
}
}