@extends(backpack_view('blank'))

@section('content')
<x-toastr />

<div class="container mt-4">
    <h3>AJAX Enrollments CRUD</h3>

    <button id="toggleFormBtn" class="btn btn-primary mb-3">Show Enrollment Form</button>

    {{-- Enrollment Form --}}
    <div id="enrollmentForm" style="display: none;" class="mb-3">
        <div id="errorBox" class="alert alert-danger d-none"></div>

        <input type="hidden" id="enrollment_id">

        <select id="user_id" class="form-select mb-2">
            <option value="">Select User</option>
        </select>

        <select id="course_id" class="form-select mb-2">
            <option value="">Select Course</option>
        </select>

        <div class="d-flex gap-2">
            <button id="addEnrollment" class="btn btn-success">Add Enrollment</button>
            <button id="resetForm" type="button" class="btn btn-secondary d-none">Reset</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="d-flex mb-3 align-items-center gap-2">
        <input type="text" id="search" class="form-control" placeholder="Search by user, email, phone, or course..." style="max-width: 300px;">
        <select id="filterUser" class="form-select" style="max-width: 200px;">
            <option value="">All Users</option>
        </select>
        <select id="filterCourse" class="form-select" style="max-width: 200px;">
            <option value="">All Courses</option>
        </select>
    </div>

    {{-- Table --}}
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>User Name</th>
                <th>User Email</th>
                <th>User Phone</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="enrollmentTable"></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const enrollmentForm = document.getElementById('enrollmentForm');
    const tableBody = document.getElementById('enrollmentTable');
    const paginationDiv = document.createElement('div');
    paginationDiv.classList.add('mt-3', 'd-flex', 'justify-content-center');
    enrollmentForm.parentElement.appendChild(paginationDiv);

    const searchInput = document.getElementById('search');
    const filterUser = document.getElementById('filterUser');
    const filterCourse = document.getElementById('filterCourse');
    const addBtn = document.getElementById('addEnrollment');
    const resetBtn = document.getElementById('resetForm');
    const idInput = document.getElementById('enrollment_id');
    const errorBox = document.getElementById('errorBox');
    const userSelect = document.getElementById('user_id');
    const courseSelect = document.getElementById('course_id');

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
        enrollmentForm.style.display = enrollmentForm.style.display === 'none' ? 'block' : 'none';
        toggleFormBtn.textContent = enrollmentForm.style.display === 'block' ? 'Hide Enrollment Form' : 'Show Enrollment Form';
    });

    // Load enrollments with pagination
    window.loadEnrollments = function(search = '', user = '', course = '', page = 1) {
        fetch(`{{ route('ajax-enrollment.index') }}?search=${encodeURIComponent(search)}&user=${user}&course=${course}&page=${page}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';
            userSelect.innerHTML = '<option value="">Select User</option>';
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            filterUser.innerHTML = '<option value="">All Users</option>';
            filterCourse.innerHTML = '<option value="">All Courses</option>';

            // Populate filters
            data.users.forEach(s => {
                const sSelected = (s.id == user) ? 'selected' : '';
                userSelect.innerHTML += `<option value="${s.id}" ${sSelected}>${s.name}</option>`;
                filterUser.innerHTML += `<option value="${s.id}" ${sSelected}>${s.name}</option>`;
            });

            data.courses.forEach(c => {
                const cSelected = (c.id == course) ? 'selected' : '';
                courseSelect.innerHTML += `<option value="${c.id}" ${cSelected}>${c.title}</option>`;
                filterCourse.innerHTML += `<option value="${c.id}" ${cSelected}>${c.title}</option>`;
            });

            // Table
            if (!data.enrollments.length) {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No enrollments found.</td></tr>`;
                paginationDiv.innerHTML = '';
                return;
            }

            data.enrollments.forEach(e => {
                tableBody.innerHTML += `
                    <tr>
                        <td><a href="/admin/user/${e.user_id}/show">${e.user?.name || '-'}</a></td>
                        <td>${e.user.email}</td>
                        <td>${e.user.phone}</td>
                        <td><a href="/admin/course/${e.course_id}/show">${e.course?.title || '-'}</a></td>
                        <td>
                            <a href="/admin/enrollment/${e.id}/show" class="btn btn-sm btn-secondary">View</a>
                            <button class="btn btn-sm btn-info" onclick="editEnrollment(${e.id}, ${e.user_id}, ${e.course_id}, '${e.user_email}', '${e.user_phone}')">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteEnrollment(${e.id})">Delete</button>
                        </td>
                    </tr>`;
            });

            // Pagination
            const { current_page, last_page } = data.pagination;
            paginationDiv.innerHTML = '';
            let paginationHTML = `<nav><ul class="pagination">`;
            for (let i = 1; i <= last_page; i++) {
                paginationHTML += `<li class="page-item ${i === current_page ? 'active' : ''}">
                    <button class="page-link" onclick="loadEnrollments('${searchInput.value.trim()}', '${filterUser.value}', '${filterCourse.value}', ${i})">${i}</button>
                </li>`;
            }
            paginationHTML += `</ul></nav>`;
            paginationDiv.innerHTML = paginationHTML;
        });
    };

    window.editEnrollment = (id, user_id, course_id) => {
        idInput.value = id;
        userSelect.value = user_id;
        courseSelect.value = course_id;
        addBtn.textContent = 'Update Enrollment';
        resetBtn.classList.remove('d-none');
        enrollmentForm.style.display = 'block';
        toggleFormBtn.textContent = 'Hide Enrollment Form';
        showErrors();
    };

    function resetForm() {
        idInput.value = '';
        userSelect.value = '';
        courseSelect.value = '';
        addBtn.textContent = 'Add Enrollment';
        resetBtn.classList.add('d-none');
        showErrors();
    }

    resetBtn.addEventListener('click', resetForm);

    // Create or Update Enrollment
    addBtn.addEventListener('click', () => {
        const user_id = userSelect.value;
        const course_id = courseSelect.value;
        const enrollment_id = idInput.value;

        const errors = [];
        if (!user_id) errors.push('User is required.');
        if (!course_id) errors.push('Course is required.');
        if (errors.length) return showErrors(errors);
        showErrors();

        const method = enrollment_id ? 'PUT' : 'POST';
        const endpoint = enrollment_id ? `/admin/ajax-enrollment/${enrollment_id}` : `{{ route('ajax-enrollment.store') }}`;

        addBtn.disabled = true;
        addBtn.textContent = enrollment_id ? 'Updating...' : 'Adding...';

        fetch(endpoint, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ user_id, course_id })
        })
        .then(res => res.json())
        .then(data => {
            addBtn.disabled = false;
            addBtn.textContent = enrollment_id ? 'Update Enrollment' : 'Add Enrollment';
            if (data.success) {
                loadEnrollments(searchInput.value, filterUser.value, filterCourse.value);
                resetForm();
                if (window.toastr) toastr.success(enrollment_id ? 'Enrollment updated!' : 'Enrollment added!');
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

    // delete
    window.deleteEnrollment = id => {
        if (!confirm('Are you sure?')) return;
        fetch(`/admin/ajax-enrollment/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadEnrollments(searchInput.value, filterUser.value, filterCourse.value);
                if (window.toastr) toastr.success('Enrollment deleted!');
            } else {
                showErrors([data.message || 'Something went wrong']);
            }
        });
    };

    // Search + Filter
    let searchTimer;
    searchInput.addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadEnrollments(e.target.value.trim(), filterUser.value, filterCourse.value), 300);
    });

    filterUser.addEventListener('change', e => loadEnrollments(searchInput.value, e.target.value, filterCourse.value));
    filterCourse.addEventListener('change', e => loadEnrollments(searchInput.value, filterUser.value, e.target.value));

    loadEnrollments();
});
</script>
@endsection
