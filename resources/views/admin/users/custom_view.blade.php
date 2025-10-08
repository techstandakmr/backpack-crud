@extends(backpack_view('blank'))

@section('content')
    <div class="container-fluid">
        <h2 class="mb-2">Users</h2>

        <a href="{{ url('admin/user/create') }}" class="btn btn-primary btn-sm mb-3">
            Add User
        </a>

        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.user.custom') }}" class="mb-3 row g-2">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Search by Name"
                    value="{{ request('name') }}">
            </div>
            <div class="col-md-4">
                <input type="text" name="email" class="form-control" placeholder="Search by Email"
                    value="{{ request('email') }}">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="{{ route('admin.user.custom') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>

        <!-- Users Table -->
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Sr.</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->created_at }}</td>
                        <td>
                            <a href="{{ url('admin/user/' . $user->id . '/edit') }}"
                                class="btn btn-sm btn-warning">Edit</a>

                            <form action="{{ url('admin/user/' . $user->id) }}" method="POST"
                                style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure?')">Delete</button>
                            </form>

                            <a href="{{ url('admin/user/' . $user->id . '/show') }}"
                                class="btn btn-sm btn-secondary">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
