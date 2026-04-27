<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; text-align: center; color: #333; }
        .border-outer { border: 10px double #6366f1; padding: 20px; height: 90%; }
        .border-inner { border: 2px solid #333; padding: 40px; height: 100%; }
        .title { color: #6366f1; text-transform: uppercase; letter-spacing: 5px; }
        .name { font-size: 40px; margin: 20px 0; }
        .course { font-size: 25px; font-weight: bold; }
        .footer { margin-top: 50px; width: 100%; }
        .col { display: inline-block; width: 30%; font-size: 12px; }
    </style>
</head>
<body>
    <div class="border-outer">
        <div class="border-inner">
            <h2 class="title">Certificate of Completion</h2>
            <p>This is to certify that</p>
            <h1 class="name">{{ $user->name }}</h1>
            <p>has successfully completed</p>
            <h2 class="course">{{ $course->title }}</h2>
            <p>on {{ $certificate->issued_at->format('M d, Y') }}</p>
            
            <div class="footer">
                <div class="col">
                    <strong>ID: {{ $certificate->certificate_number }}</strong>
                </div>
                <div class="col" style="border-top: 1px solid #ccc; padding-top: 5px;">
                    Authorized Signature
                </div>
                <div class="col">
                    <strong>{{ config('app.name') }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>