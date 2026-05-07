<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public function show($id)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        return Inertia::render('Certificate/Show', [
            'certificate' => $certificate,
        ]);
    }
    
    public function download($id)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        
        $pdf = Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
        ]);
        
        return $pdf->download("certificate-{$certificate->certificate_number}.pdf");
    }
    
    public function verify($number)
    {
        $certificate = Certificate::with(['user', 'course'])
            ->where('certificate_number', $number)
            ->firstOrFail();
        
        return Inertia::render('Certificate/Verify', [
            'certificate' => $certificate,
            'valid' => true,
        ]);
    }
}