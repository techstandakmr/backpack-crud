@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <h2 class="mb-2">Resources</h2>

        <a href="{{ url('admin/resource/create') }}" class="btn btn-primary btn-sm mb-3 text-lg">
            Add
        </a>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.resource.custom') }}" class="mb-3 d-flex gap-2 align-items-center">
            <input type="text" name="name" placeholder="Search by Name" value="{{ request('name') }}"
                class="form-control w-auto">
            <input type="text" name="lesson_title" placeholder="Search by Lesson Title"
                value="{{ request('lesson_title') }}" class="form-control w-auto">
            <input type="text" name="url" placeholder="Search by URL" value="{{ request('url') }}"
                class="form-control w-auto">

            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('admin.resource.custom') }}" class="btn btn-secondary btn-sm">Reset</a>
        </form>

        <!-- Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Sr.</th>
                    <th>Name</th>
                    <th>Lesson</th>
                    <th>URL</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($resources as $resource)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $resource->name }}</td>
                        <td>{{ $resource->lesson ? $resource->lesson->title : 'N/A' }}</td>
                        <td>
                            <a href="{{ $resource->url }}" target="_blank">{{ $resource->url }}</a>
                        </td>
                        <td>{{ $resource->created_at }}</td>
                        <td>
                            <a href="{{ url('admin/resource/' . $resource->id . '/show') }}"
                                class="btn btn-sm btn-secondary">View</a>
                            <a href="{{ url('admin/resource/' . $resource->id . '/edit') }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ url('admin/resource/' . $resource->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No resources found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
