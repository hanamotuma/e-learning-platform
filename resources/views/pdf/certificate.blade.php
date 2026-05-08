<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            size: landscape;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Georgia', serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .certificate {
            width: 900px;
            margin: 20px auto;
            background: white;
            padding: 40px;
            border: 15px solid #667eea;
            position: relative;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            border-radius: 20px;
        }
        .certificate h1 {
            font-size: 48px;
            text-align: center;
            color: #667eea;
            margin-top: 20px;
            letter-spacing: 4px;
        }
        .certificate p {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin: 20px 0;
        }
        .student-name {
            font-size: 48px;
            font-weight: bold;
            text-align: center;
            color: #333;
            margin: 40px 0;
            font-family: 'Courier New', monospace;
            border-bottom: 2px solid #667eea;
            display: inline-block;
            width: auto;
            padding: 0 30px;
        }
        .course-name {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            color: #764ba2;
            margin: 20px 0;
        }
        .signature {
            margin-top: 60px;
            text-align: center;
        }
        .signature-line {
            width: 300px;
            border-top: 1px solid #333;
            margin: 0 auto;
        }
        .date {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
        }
        .certificate-number {
            position: absolute;
            bottom: 20px;
            right: 30px;
            font-size: 10px;
            color: #999;
        }
        .seal {
            position: absolute;
            bottom: 40px;
            left: 40px;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 2px solid #667eea;
            text-align: center;
            line-height: 100px;
            color: #667eea;
            font-size: 14px;
        }
        .center-content {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <h1>CERTIFICATE OF COMPLETION</h1>
        <p>This certificate is proudly presented to</p>
        <div class="center-content">
            <div class="student-name">{{ $user->full_name ?? $user->name }}</div>
        </div>
        <p>for successfully completing the course</p>
        <div class="course-name">{{ $course->title }}</div>
        <p>with outstanding dedication and achievement</p>
        
        <div class="signature">
            <div class="signature-line"></div>
            <p>Hana Motuma<br><small>Director of Education</small></p>
        </div>
        
        <div class="date">
            Date of Issue: {{ $date }}
        </div>
        
        <div class="certificate-number">
            Certificate ID: {{ $certificate_number }}
        </div>
        
        <div class="seal">
            LearnHub<br>Seal
        </div>
    </div>
</body>
</html>