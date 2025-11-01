<table>
    <thead>
        <tr>
            <th colspan="4">{{ $user->name }} ({{ ucfirst($user->role) }})</th>
        </tr>
    </thead>
    @if ($user->role === 'student')
        <thead>
            <tr>
                <th>Enrollment ID</th>
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
    @elseif ($user->role === 'teacher')
        <thead>
            <tr>
                <th>Course ID</th>
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
    @endif
</table>
