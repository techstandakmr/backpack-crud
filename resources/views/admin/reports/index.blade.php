@extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Course Enrollments Report</h3>

        <form method="GET" action="{{ route('report.index') }}" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="course_id" class="form-control">
                        <option value="">-- Select Course --</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">Filter</button>
                </div>
                @if ($courseId)
                    <div class="col-md-2">
                        <div class="btn-group">
                            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('report.export', ['course_id' => $courseId, 'export_type' => 'csv']) }}">
                                        Export as CSV
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item"
                                        href="{{ route('report.export', ['course_id' => $courseId, 'export_type' => 'excel']) }}">
                                        Export as Excel
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </form>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Enrolled At</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enrollments as $enroll)
                    <tr>
                        <td><a href="{{ url('admin/enrollment/' . $course['id'] . '/show') }}">{{ $enroll->id }}</a></td>
                        <td>{{ $enroll->student_name }}</td>
                        <td>{{ $enroll->student_email }}</td>
                        <td>{{ $enroll->phone }}</td>
                        <td>{{ $enroll->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No enrollments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
