<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Course Enrollments PDF Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
        h2, h3 { text-align: center; }
        .footer { text-align: center; font-size: 11px; margin-top: 20px; color: #777; }
    </style>
</head>
<body>
    <h2>Course Enrollments Report</h2>
    <h3>{{ $course->title ?? 'All Courses' }}</h3>

    <table>
        <thead>
            <tr>
                <th>Serial No.</th>
                <th>Enrollment ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Enrolled At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($enrollments as $index => $enroll)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $enroll->id }}</td>
                    <td>{{ $enroll->user->name ?? $enroll->student_name }}</td>
                    <td>{{ $enroll->user->email ?? $enroll->student_email }}</td>
                    <td>{{ $enroll->user->phone ?? $enroll->phone }}</td>
                    <td>{{ $enroll->created_at->format('d M Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
