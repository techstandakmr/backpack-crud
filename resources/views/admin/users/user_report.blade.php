<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $user->name }} Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h2 { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #444; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h2>User Report ({{ ucfirst($user->role) }})</h2>

    <p><strong>Name:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>

    @if ($user->role === 'student')
        <h3>Enrolled Courses</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Title</th>
                    <th>Description</th>
                    <th>Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($user->enrollments as $enroll)
                    <tr>
                        <td>{{ $enroll->id }}</td>
                        <td>{{ $enroll->course->title ?? 'N/A' }}</td>
                        <td>{{ $enroll->course->description ?? 'N/A' }}</td>
                        <td>{{ $enroll->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @elseif ($user->role === 'teacher')
        <h3>Authored Courses</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($user->coursesAuthored as $course)
                    <tr>
                        <td>{{ $course->id }}</td>
                        <td>{{ $course->title }}</td>
                        <td>{{ $course->description }}</td>
                        <td>{{ $course->created_at->format('Y-m-d') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
