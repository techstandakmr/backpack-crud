@php
    $lessonSearch = request()->get('lesson_search');
    $enrollSearch = request()->get('enroll_search');
    $lessonPage = request()->get('lesson_page');
    $enrollPage = request()->get('enroll_page');

    $enrollmentsQuery = $entry->enrollments();
    if ($enrollSearch) {
        $enrollmentsQuery->where(function ($query) use ($enrollSearch) {
            $query->where('student_name', 'like', "%{$enrollSearch}%")
                  ->orWhere('student_email', 'like', "%{$enrollSearch}%")
                  ->orWhere('phone', 'like', "%{$enrollSearch}%")
                  ->orWhereHas('course', function ($q) use ($enrollSearch) {
                      $q->where('title', 'like', "%{$enrollSearch}%");
                  });
        });
    }

    $enrollments = $enrollmentsQuery->paginate(10, ['*'], 'enroll_page')->appends(request()->query());
@endphp

<div class="card mt-4">
    <div class="card-header"><h4>Enrollments</h4></div>
    <div class="card-body">

        {{--  Search Form --}}
        <div class="mb-2">
            <form method="GET" class="form-inline">
                <input type="text" name="enroll_search" value="{{ e($enrollSearch) }}" class="form-control form-control-sm p-2 mb-2" placeholder="Search Enrollments">

                @if($lessonSearch)
                    <input type="hidden" name="lesson_search" value="{{ e($lessonSearch) }}">
                @endif
                @if($lessonPage)
                    <input type="hidden" name="lesson_page" value="{{ e($lessonPage) }}">
                @endif
                @if($enrollPage)
                    <input type="hidden" name="enroll_page" value="{{ e($enrollPage) }}">
                @endif

                <button class="btn btn-sm btn-primary mr-2">Search</button>

                @php
                    $keepForEnrollReset = request()->except(['enroll_search', 'enroll_page']);
                    $enrollResetQuery = count($keepForEnrollReset) ? '?' . http_build_query($keepForEnrollReset) : '';
                @endphp
                @if($enrollSearch || $enrollPage)
                    <a href="{{ url()->current() }}{{ $enrollResetQuery }}" class="btn btn-sm btn-secondary">Reset Enrollments</a>
                @endif
            </form>
        </div>

        @if($enrollments->isEmpty())
            <em class="text-muted">No enrollments found for your search.</em>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Sr.</th>
                            <th>Course Title</th>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Phone</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serial = ($enrollments->currentPage() - 1) * $enrollments->perPage() + 1; @endphp
                        @foreach($enrollments as $enrollment)
                            <tr>
                                <td><a href="{{ backpack_url('enrollment/'.$enrollment->id.'/show') }}">{{ $serial++ }}</a></td>
                                <td><a href="{{ backpack_url('course/'.$enrollment->course_id.'/show') }}">{{ e($enrollment->course->title) }}</a></td>
                                <td>{{ e($enrollment->student_name) }}</td>
                                <td>{{ e($enrollment->student_email) }}</td>
                                <td>{{ e($enrollment->phone ?? 'N/A') }}</td>
                                <td>{{ $enrollment->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $enrollment->updated_at->format('M d, Y H:i') }}</td>
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