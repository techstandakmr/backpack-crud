@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <h2 class="mb-2">Lessons</h2>
        <a href="{{ url('admin/lesson/create') }}" class="btn btn-primary btn-sm mb-3 text-lg">
            Add
        </a>

        <form method="GET" action="{{ route('admin.lesson.custom') }}" class="mb-3 row g-2">
            <div class="col-md-4">
                <input type="text" name="title" class="form-control" placeholder="Search by Lesson Title"
                    value="{{ request('title') }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="course" class="form-control" placeholder="Search by Course Title"
                    value="{{ request('course') }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="content" class="form-control" placeholder="Search by Content"
                    value="{{ request('content') }}">
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.lesson.custom') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Sr.</th>
                    <th>Title</th>
                    <th>Course</th>
                    <th>Content</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lessons as $lesson)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $lesson->title }}</td>
                        <td>{{ $lesson->course->title ?? 'N/A' }}</td>
                        <td>{{ Str::limit($lesson->content, 50) }}</td>
                        <td>{{ $lesson->created_at }}</td>
                        <td>
                            <a href="{{ url('admin/lesson/' . $lesson->id . '/show') }}"
                                class="btn btn-sm btn-secondary">View</a>
                            <a href="{{ url('admin/lesson/' . $lesson->id . '/edit') }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ url('admin/lesson/' . $lesson->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
