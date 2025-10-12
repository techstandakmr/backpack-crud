@extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h3 class="mb-4">Course Enrollments Report</h3>

        <div class="d-flex justify-content-start mb-3">
            <form method="GET" action="{{ route('report.index') }}" class="w-auto mb-4">
                <div class="row w-auto">
                    <div class="w-auto">
                        <div class="dropdown w-auto">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="courseDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                {{ $courseId ? $courses->firstWhere('id', $courseId)->title : '-- Select Course --' }}
                            </button>

                            <ul class="dropdown-menu p-2" aria-labelledby="courseDropdown"
                                style="max-height: 300px; overflow-y: auto;">
                                <input type="text" id="courseSearch" class="form-control mb-2"
                                    placeholder="Search course...">

                                @foreach ($courses as $course)
                                    <li>
                                        <a class="dropdown-item course-item" href="#" data-id="{{ $course->id }}">
                                            {{ $course->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <input type="hidden" name="course_id" id="selectedCourseId" value="{{ $courseId }}">



                        {{-- <select name="course_id" class="form-control">
                            <option value="">-- Select Course --</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                                    {{ $course->title }}
                                </option>
                            @endforeach
                        </select> --}}
                    </div>
                    <div class="w-auto">
                        <button class="btn btn-primary" type="submit">Filter</button>
                    </div>
                </div>
            </form>
            @if ($courseId)
                <div class="w-auto ms-2">
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
                            <li>
                                <a class="dropdown-item"
                                    href="{{ route('report.export', ['course_id' => $courseId, 'export_type' => 'pdf']) }}">
                                    Export as PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
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
                @forelse($enrollments as $enroll)
                    <tr>
                        <td>{{ $loop->iteration }} </td>
                        <td><a href="{{ url('admin/enrollment/' . $enroll->id . '/show') }}">{{ $enroll->id }}</a>
                        </td>
                        <td>
                            <a href="{{ url('admin/user/' . $enroll->user->id . '/show') }}">{{ $enroll->user->name }}</a>
                        </td>
                        <td>{{ $enroll->user->email }}</td>
                        <td>{{ $enroll->user->phone }}</td>
                        <td>{{ $enroll->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No enrollments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="d-flex justify-content-center mt-3">
            {{ $enrollments->links() }}
        </div>
    </div>
@endsection

@push('after_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('courseSearch');
            const courseItems = document.querySelectorAll('.course-item');
            const dropdownButton = document.getElementById('courseDropdown');
            const selectedCourseId = document.getElementById('selectedCourseId');

            // Live search filter
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                courseItems.forEach(item => {
                    const text = item.textContent.toLowerCase();
                    item.style.display = text.includes(filter) ? '' : 'none';
                });
            });

            // Select course
            courseItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    const title = this.textContent;

                    selectedCourseId.value = id;
                    dropdownButton.textContent = title;
                });
            });
        });
    </script>
@endpush
