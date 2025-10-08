@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <h2 class="mb-2">Courses</h2>
        <a href="{{ url('admin/course/create') }}" class="btn btn-primary btn-sm mb-3 text-lg">
            Add
        </a>

        <form method="GET" action="{{ route('admin.course.custom') }}" class="mb-3 row g-2">
            <div class="col-md-3">
                <input type="text" name="title" class="form-control" placeholder="Search by Title"
                    value="{{ request('title') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="description" class="form-control" placeholder="Search by Description"
                    value="{{ request('description') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="author_name" class="form-control" placeholder="Search by Author Name"
                    value="{{ request('author_name') }}">
            </div>
            <div class="col-md-3">
                <input type="text" name="lesson_title" class="form-control" placeholder="Search by Lesson Title"
                    value="{{ request('lesson_title') }}">
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.course.custom') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    {{-- serial number --}}
                    <th>Sr.</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Author</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($courses as $course)
                    <tr>
                        <td>{{ $loop->iteration }}</td> 
                        <td>{{ $course['title'] }}</td>
                        <td>{{ Str::limit($course['description'], 80) }}</td> {{-- ✅ short description --}}
                        <td>{{ $course['author']['name'] ?? 'N/A' }}</td>
                        <td>{{ $course['created_at'] }}</td>
                        <td>
                            <button class="btn btn-sm btn-info" type="button" data-bs-toggle="collapse"
                                data-bs-target="#details-{{ $course['id'] }}">
                                Show Details
                            </button>

                            <a href="{{ url('admin/course/' . $course['id'] . '/edit') }}" class="btn btn-sm btn-warning">
                                Edit
                            </a>

                            <form action="{{ url('admin/course/' . $course['id']) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>
                            </form>

                            <a href="{{ url('admin/course/' . $course['id'] . '/show') }}"
                                class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>

                    <!-- Collapsible details row -->
                    <tr id="details-{{ $course['id'] }}" class="collapse">
                        <td colspan="5">
                            <div class="mb-2"><strong>Description:</strong> {{ $course['description'] }}</div>

                            <div class="mb-2">
                                <strong>Lessons:</strong>
                                @if (count($course['lessons']) > 0)
                                    <ul>
                                        @foreach ($course['lessons'] as $lesson)
                                            <li>{{ $lesson['title'] }} ({{ $lesson['created_at'] }})</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No lessons added.</p>
                                @endif
                            </div>

                            <div>
                                <strong>Enrollments:</strong>
                                @if (count($course['enrollments']) > 0)
                                    <ul>
                                        @foreach ($course['enrollments'] as $enroll)
                                            <li>{{ $enroll['student_name'] }} - {{ $enroll['student_email'] }} -
                                                {{ $enroll['phone'] ?? 'N/A' }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p>No enrollments yet.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
