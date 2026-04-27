<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment; // Essential import
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    /**
     * Display the certificate page in Vue
     */
    public function show($courseId)
    {
        $userId = auth()->id();
        
        // 1. First, find the specific enrollment record for this user and course.
        // This ensures the user is actually enrolled and gives us the enrollment_id.
        $enrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->firstOrFail(); // Throws 404 if not enrolled

        // 2. Use firstOrCreate with enrollment_id included in the creation array.
        $certificate = Certificate::firstOrCreate(
            [
                'user_id' => $userId, 
                'course_id' => $courseId
            ],
            [
                'enrollment_id' => $enrollment->id, // 🔥 FIXED: Providing the missing required field
                'certificate_number' => 'CERT-' . strtoupper(Str::random(12)),
                'issued_at' => now(),
                'metadata' => ['platform' => 'E-Learning Pro']
            ]
        );

        return Inertia::render('Admin/Certificate/Show', [
            'certificate' => $certificate->load(['enrollment.user', 'enrollment.course'])
        ]);
    }

    /**
     * Download the Certificate as PDF
     */
    public function download(Certificate $certificate)
    {
        // Safety: Ensure user owns this certificate
        if ($certificate->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to certificate.');
        }

        $certificate->load(['enrollment.user', 'enrollment.course']);

        // Generate PDF using the landscape orientation common for certificates
        $pdf = Pdf::loadView('pdf.certificate_template', [
            'certificate' => $certificate,
            'user' => auth()->user(),
            'course' => $certificate->enrollment->course
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Certificate-{$certificate->certificate_number}.pdf");
    }
}