@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <h2 class="mb-2">Enrollments</h2>
        <a href="{{ url('admin/enrollment/create') }}" class="btn btn-primary btn-sm mb-3 text-lg">
            Add
        </a>
        <form method="GET" action="{{ route('admin.enrollment.custom') }}" class="mb-3 row g-2">
            <div class="col-md-3">
                <input type="text" name="student_name" class="form-control" placeholder="Student Name"
                    value="{{ request('student_name') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="student_email" class="form-control" placeholder="Email"
                    value="{{ request('student_email') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="course_title" class="form-control" placeholder="Course Title"
                    value="{{ request('course_title') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="phone" class="form-control" placeholder="Phone"
                    value="{{ request('phone') }}">
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.enrollment.custom') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>


        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    {{-- serial number --}}
                    <th>Sr.</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Phone</th>
                    <th>Enrolled At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enrollments as $enrollment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $enrollment->student_name }}</td>
                        <td>{{ $enrollment->student_email }}</td>
                        <td>{{ $enrollment->course->title ?? 'N/A' }}</td>
                        <td>{{ $enrollment->phone ?? 'N/A' }}</td>
                        <td>{{ $enrollment->created_at }}</td>
                        <td>
                            <a href="{{ url('admin/enrollment/' . $enrollment->id . '/edit') }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ url('admin/enrollment/' . $enrollment->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>

                            <a href="{{ url('admin/enrollment/' . $enrollment->id) . '/show' }}"
                                class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
