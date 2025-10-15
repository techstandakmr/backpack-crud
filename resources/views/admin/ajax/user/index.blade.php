@extends(backpack_view('blank'))

@section('content')
<x-toastr />

<div class="container mt-4">
    <h3>AJAX Users CRUD</h3>

    <button id="toggleFormBtn" class="btn btn-primary mb-3">Show User Form</button>

    {{-- User Form --}}
    <div id="userForm" style="display: none;" class="mb-3">
        <div id="errorBox" class="alert alert-danger d-none"></div>

        <input type="hidden" id="user_id">

        <input type="text" id="name" class="form-control mb-2" placeholder="Name">
        <input type="email" id="email" class="form-control mb-2" placeholder="Email">
        <input type="text" id="phone" class="form-control mb-2" placeholder="Phone">
        <input type="password" id="password" class="form-control mb-2" placeholder="Password">
        <select id="role" class="form-select mb-2">
            <option value="">Select Role</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
        </select>

        <div class="d-flex gap-2">
            <button id="addUser" class="btn btn-success">Add User</button>
            <button id="resetForm" type="button" class="btn btn-secondary d-none">Reset</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="d-flex mb-3 align-items-center gap-2">
        <input type="text" id="search" class="form-control" placeholder="Search by name, email, or phone..." style="max-width: 300px;">
        <select id="filterRole" class="form-select" style="max-width: 200px;">
            <option value="">All Roles</option>
            <option value="student">Student</option>
            <option value="teacher">Teacher</option>
            <option value="admin">Admin</option>
        </select>
    </div>

    {{-- Table --}}
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="userTable"></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const userForm = document.getElementById('userForm');
    const tableBody = document.getElementById('userTable');
    const paginationDiv = document.createElement('div');
    paginationDiv.classList.add('mt-3', 'd-flex', 'justify-content-center');
    userForm.parentElement.appendChild(paginationDiv);

    const searchInput = document.getElementById('search');
    const filterRole = document.getElementById('filterRole');
    const addBtn = document.getElementById('addUser');
    const resetBtn = document.getElementById('resetForm');
    const idInput = document.getElementById('user_id');
    const errorBox = document.getElementById('errorBox');

    function showErrors(messages = []) {
        if (!messages.length) {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
            return;
        }
        errorBox.classList.remove('d-none');
        errorBox.innerHTML = messages.map(msg => `<div>${msg}</div>`).join('');
    }

    toggleFormBtn.addEventListener('click', () => {
        resetForm();
        userForm.style.display = userForm.style.display === 'none' ? 'block' : 'none';
        toggleFormBtn.textContent = userForm.style.display === 'block' ? 'Hide User Form' : 'Show User Form';
    });

    // Load users with pagination
    window.loadUsers = function(search = '', role = '', page = 1) {
        fetch(`{{ route('ajax-user.index') }}?search=${encodeURIComponent(search)}&role=${role}&page=${page}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';

            if (!data.users.length) {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No users found.</td></tr>`;
                paginationDiv.innerHTML = '';
                return;
            }

            data.users.forEach(u => {
                tableBody.innerHTML += `
                    <tr>
                        <td><a href="/admin/user/${u.id}/show">${u.name}</a></td>
                        <td>${u.email}</td>
                        <td>${u.phone || '-'}</td>
                        <td>${u.role}</td>
                        <td>
                            <a href="/admin/user/${u.id}/show" class="btn btn-sm btn-secondary">View</a>
                            <button class="btn btn-sm btn-info" onclick="editUser(${u.id}, '${u.name}', '${u.email}', '${u.phone}', '${u.role}')">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteUser(${u.id})">Delete</button>
                        </td>
                    </tr>`;
            });

            // Pagination
            const { current_page, last_page } = data.pagination;
            paginationDiv.innerHTML = '';
            let paginationHTML = `<nav><ul class="pagination">`;
            for (let i = 1; i <= last_page; i++) {
                paginationHTML += `<li class="page-item ${i === current_page ? 'active' : ''}">
                    <button class="page-link" onclick="loadUsers('${searchInput.value.trim()}', '${filterRole.value}', ${i})">${i}</button>
                </li>`;
            }
            paginationHTML += `</ul></nav>`;
            paginationDiv.innerHTML = paginationHTML;
        });
    };

    window.editUser = (id, name, email, phone, role) => {
        idInput.value = id;
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('phone').value = phone;
        document.getElementById('password').value = '';
        document.getElementById('role').value = role;
        addBtn.textContent = 'Update User';
        resetBtn.classList.remove('d-none');
        userForm.style.display = 'block';
        toggleFormBtn.textContent = 'Hide User Form';
        showErrors();
    };

    function resetForm() {
        idInput.value = '';
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('phone').value = '';
        document.getElementById('password').value = '';
        document.getElementById('role').value = '';
        addBtn.textContent = 'Add User';
        resetBtn.classList.add('d-none');
        showErrors();
    }

    resetBtn.addEventListener('click', resetForm);

    // Create or Update User
    addBtn.addEventListener('click', () => {
        const id = idInput.value;
        const name = document.getElementById('name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value.trim();
        const role = document.getElementById('role').value;

        const errors = [];
        if (!name) errors.push('Name is required.');
        if (!email) errors.push('Email is required.');
        if (!role) errors.push('Role is required.');
        if (!id && !password) errors.push('Password is required for new users.');
        if (errors.length) return showErrors(errors);
        showErrors();

        const method = id ? 'PUT' : 'POST';
        const endpoint = id ? `/admin/ajax-user/${id}` : `{{ route('ajax-user.store') }}`;

        addBtn.disabled = true;
        addBtn.textContent = id ? 'Updating...' : 'Adding...';

        fetch(endpoint, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name, email, phone, password, role })
        })
        .then(res => res.json())
        .then(data => {
            addBtn.disabled = false;
            addBtn.textContent = id ? 'Update User' : 'Add User';
            if (data.success) {
                loadUsers(searchInput.value, filterRole.value);
                resetForm();
                if (window.toastr) toastr.success(id ? 'User updated!' : 'User added!');
            } else {
                showErrors([data.message || 'Something went wrong']);
            }
        })
        .catch(err => {
            console.error(err);
            addBtn.disabled = false;
            showErrors(['Network/server error']);
        });
    });

    // Delete
    window.deleteUser = id => {
        if (!confirm('Are you sure?')) return;
        fetch(`/admin/ajax-user/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadUsers(searchInput.value, filterRole.value);
                if (window.toastr) toastr.success('User deleted!');
            } else {
                showErrors([data.message || 'Something went wrong']);
            }
        });
    };

    // Search + Filter
    let searchTimer;
    searchInput.addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadUsers(e.target.value.trim(), filterRole.value), 300);
    });

    filterRole.addEventListener('change', e => loadUsers(searchInput.value, e.target.value));

    loadUsers();
});
</script>
@endsection
