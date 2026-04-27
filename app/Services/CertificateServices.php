<?php

namespace App\Services;

use App\Models\Certificate;
use Illuminate\Support\Str;

class CertificateService
{
    public function generate($userId, $courseId, $enrollmentId)
    {
        // prevent duplicate certificates
        $exists = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        if ($exists) {
            return $exists;
        }

        return Certificate::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'enrollment_id' => $enrollmentId,
            'certificate_number' => strtoupper(Str::random(10)),
            'certificate_url' => null, // later PDF
            'issued_at' => now(),
        ]);
    }
}