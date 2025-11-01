@php
    $courseSearch = request()->get('course_search');
    $coursePage = request()->get('course_page');

    // Decide which courses to show based on user role
    if ($entry->role === 'student') {
        $query = $entry->coursesEnrolled();
    } elseif ($entry->role === 'teacher') {
        $query = $entry->coursesAuthored();
    }

    // Search
    if ($courseSearch) {
        $query->where(function ($q) use ($courseSearch) {
            $q->where('title', 'like', "%{$courseSearch}%")->orWhere('description', 'like', "%{$courseSearch}%");
        });
    }

    // Pagination
    $courses = $query->paginate(10, ['*'], 'course_page')->appends(request()->query());
@endphp

<div class="card mt-4">
    <div class="card-header">
        <h4>Courses</h4>
    </div>

    <div class="card-body">
        {{-- Search Form --}}
        @if(!$courses->isEmpty() || $courseSearch)
        <div class="mb-2">
            <form method="GET" class="form-inline">
                <input type="text" name="course_search" value="{{ e($courseSearch) }}"
                    class="p-2 form-control form-control-sm mb-2" placeholder="Search Courses by Title or Description">

                @if ($coursePage)
                    <input type="hidden" name="course_page" value="{{ e($coursePage) }}">
                @endif

                <button class="btn btn-sm btn-primary mr-2">Search</button>

                @php
                    $resetQuery = request()->except(['course_search', 'course_page']);
                    $resetUrl = count($resetQuery) ? '?' . http_build_query($resetQuery) : '';
                @endphp

                @if ($courseSearch || $coursePage)
                    <a href="{{ url()->current() }}{{ $resetUrl }}" class="btn btn-sm btn-secondary">Reset</a>
                @endif
            </form>
        </div>
        @endif

        @if ($courses->isEmpty())
            <em class="text-muted">No courses found.</em>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Serial No.</th>
                            <th>Course ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serial = ($courses->currentPage() - 1) * $courses->perPage() + 1; @endphp
                        @foreach ($courses as $course)
                            <tr>
                                <td>{{ $serial++ }}</td>
                                <td>
                                    <a href="{{ backpack_url('course/' . $course->id . '/show') }}">
                                        {{ e($course->id) }}
                                    </a>
                                </td>
                                <td>{{ e($course->title) }}</td>
                                <td>{{ Str::limit($course->description, 40) }}</td>
                                <td>{{ $course->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {!! $courses->links('pagination::bootstrap-4')->render() !!}
            </div>
        @endif
    </div>
</div>


<div class="card mt-4">
@if ($entry->role === 'student')
        @php
            
// Get user's enrollments with course
$enrollmentsQuery = $entry->enrollments()->with('course');

$enrollments = $enrollmentsQuery->paginate(10, ['*'], 'enroll_page')->appends(request()->query());
        @endphp

        <div class="card mt-4">
            <div class="card-header">
                <h4>Enrollments</h4>
            </div>

            <div class="card-body">
            
                @if ($enrollments->isEmpty())
                    <em class="text-muted">No enrollments found.</em>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>Serial No.</th>
                                    <th>Enrollment ID</th>
                                    <th>Course</th>
                                    <th>Enrolled At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $serial = ($enrollments->currentPage() - 1) * $enrollments->perPage() + 1; @endphp
                                @foreach ($enrollments as $enroll)
                                    <tr>
                                        <td>{{ $serial++ }}</td>
                                        <td>
                                            <a href="{{ backpack_url('enrollment/' . $enroll->id . '/show') }}">
                                                {{ e($enroll->id) }}
                                            </a>
                                        </td>
                                        <td>{{ $enroll->course->title ?? 'N/A' }}</td>
                                        
                                        <td>{{ $enroll->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 d-flex justify-content-center">
                        {!! $enrollments->links('pagination::bootstrap-4')->render() !!}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
