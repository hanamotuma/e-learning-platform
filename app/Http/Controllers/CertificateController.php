<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use PDF;

class CertificateController extends Controller
{
    public function index()
    {
        $certificates = Certificate::with(['course'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Certificate/Index', [
            'certificates' => $certificates,
        ]);
    }
    
    public function generate($courseId)
    {
        $user = Auth::user();
        $course = Course::findOrFail($courseId);
        
        // Check if user is enrolled
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
        
        if (!$enrollment) {
            return redirect()->back()->with('error', 'You are not enrolled in this course');
        }
        
        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->first();
        
        if ($existingCertificate) {
            return redirect()->route('certificate.download', $existingCertificate->id);
        }
        
        // Generate certificate number
        $certificateNumber = 'CERT-' . strtoupper(uniqid()) . '-' . $user->id;
        
        // Create certificate record
        $certificate = Certificate::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'enrollment_id' => $enrollment->id,
            'certificate_number' => $certificateNumber,
            'issued_at' => now(),
            'certificate_url' => null,
        ]);
        
        return redirect()->route('certificate.download', $certificate->id);
    }
    
    public function download($id)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        $pdf = PDF::loadView('pdf.certificate', [
            'user' => $certificate->user,
            'course' => $certificate->course,
            'certificate_number' => $certificate->certificate_number,
            'date' => $certificate->issued_at->format('F j, Y'),
        ]);
        
        return $pdf->download("Certificate-{$certificate->course->title}.pdf");
    }
}