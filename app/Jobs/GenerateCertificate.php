<?php

namespace App\Jobs;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerateCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $certificate;

    /**
     * Create a new job instance.
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $certificate = $this->certificate;
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        $course = $enrollment->course;

        $data = [
            'user' => $user,
            'course' => $course,
            'certificate' => $certificate,
            'issued_at' => $certificate->issued_at->format('F j, Y'),
        ];

        $pdf = Pdf::loadView('certificates.template', $data);

        $filename = 'certificates/' . $certificate->certificate_number . '.pdf';
        Storage::put($filename, $pdf->output());

        $certificate->update([
            'pdf_url' => Storage::url($filename),
        ]);
    }
}