@php
    $lessonSearch = request()->get('lesson_search');
    $enrollSearch = request()->get('enroll_search');
    $lessonPage = request()->get('lesson_page');
    $enrollPage = request()->get('enroll_page');

    $lessonsQuery = $entry->lessons();
    if ($lessonSearch) {
        $lessonsQuery->where(function ($query) use ($lessonSearch) {
            $query->where('title', 'like', "%{$lessonSearch}%")
                  ->orWhere('content', 'like', "%{$lessonSearch}%")
                  ->orWhereHas('course', function ($q) use ($lessonSearch) {
                      $q->where('title', 'like', "%{$lessonSearch}%");
                  });
        });
    }

    $lessons = $lessonsQuery->paginate(10, ['*'], 'lesson_page')->appends(request()->query());
@endphp

<div class="card mt-4">
    <div class="card-header"><h4>Lessons</h4></div>
    <div class="card-body">

        {{-- 🔍 Search Form --}}
        <div class="mb-2">
            <form method="GET" class="form-inline">
                <input type="text" name="lesson_search" value="{{ e($lessonSearch) }}" class="p-2 form-control form-control-sm mb-2" placeholder="Search Lessons">

                @if($enrollSearch)
                    <input type="hidden" name="enroll_search" value="{{ e($enrollSearch) }}">
                @endif
                @if($lessonPage)
                    <input type="hidden" name="lesson_page" value="{{ e($lessonPage) }}">
                @endif
                @if($enrollPage)
                    <input type="hidden" name="enroll_page" value="{{ e($enrollPage) }}">
                @endif

                <button class="btn btn-sm btn-primary mr-2">Search</button>

                @php
                    $keepForLessonReset = request()->except(['lesson_search', 'lesson_page']);
                    $lessonResetQuery = count($keepForLessonReset) ? '?' . http_build_query($keepForLessonReset) : '';
                @endphp
                @if($lessonSearch || $lessonPage)
                    <a href="{{ url()->current() }}{{ $lessonResetQuery }}" class="btn btn-sm btn-secondary">Reset Lessons</a>
                @endif
            </form>
        </div>

        @if($lessons->isEmpty())
            <em class="text-muted">No lessons found for your search.</em>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Sr.</th>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Course</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $serial = ($lessons->currentPage() - 1) * $lessons->perPage() + 1; @endphp
                        @foreach($lessons as $lesson)
                            <tr>
                                <td><a href="{{ backpack_url('lesson/'.$lesson->id.'/show') }}">{{ $serial++ }}</a></td>
                                <td>{{ e($lesson->title) }}</td>
                                <td>{{ $lessonSearch ? $lesson->content : Str::limit($lesson->content, 50) }}</td>
                                <td><a href="{{ backpack_url('course/'.$lesson->course_id.'/show') }}">{{ e($lesson->course->title) }}</a></td>
                                <td>{{ $lesson->created_at->format('M d, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-center">
                {!! $lessons->links('pagination::bootstrap-4')->render() !!}
            </div>
        @endif
    </div>
</div>