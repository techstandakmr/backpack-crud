@extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h3>AJAX Courses CRUD</h3>
        {{-- Add course --}}
        <button id="toggleFormBtn" class="btn btn-primary mb-3">Show Course Form</button>

        <div id="courseForm" class="mb-3" style="display: none;">
            <div id="errorBox" class="alert alert-danger d-none"></div> <!-- 🔹 For validation errors -->

            <input type="hidden" id="course_id">
            <input type="text" id="title" class="form-control mb-2" placeholder="Course title">
            <textarea id="description" class="form-control mb-2" placeholder="Course description"></textarea>
            <select id="author_id" class="form-control mb-2">
                <option value="">Select Author (Teacher)</option>
            </select>
            <div class="d-flex gap-2">
                <button id="addCourse" class="btn btn-success">Add Course</button>
                <button id="resetForm" type="button" class="btn btn-secondary d-none">Reset</button>
            </div>
        </div>
        <div class="d-flex mb-3 align-items-center gap-2">
            <input type="text" id="search" class="form-control"
                placeholder="Search by title, description, lesson, or author..." style="max-width: 300px;">
            <select id="filterAuthor" class="form-select" style="max-width: 250px;">
                <option value="">All Authors</option>
            </select>
        </div>

        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Author</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="courseTable"></tbody>
        </table>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleFormBtn = document.getElementById('toggleFormBtn');
            const courseForm = document.getElementById('courseForm');
            const authorSelect = document.getElementById('author_id');
            const filterAuthor = document.getElementById('filterAuthor');
            const tableBody = document.getElementById('courseTable');
            const searchInput = document.getElementById('search');
            const addBtn = document.getElementById('addCourse');
            const resetBtn = document.getElementById('resetForm');
            const idInput = document.getElementById('course_id');
            const errorBox = document.getElementById('errorBox');

            // Helper to show error messages above form
            function showErrors(messages = []) {
                if (!messages.length) {
                    errorBox.classList.add('d-none');
                    errorBox.innerHTML = '';
                    return;
                }
                errorBox.classList.remove('d-none');
                errorBox.innerHTML = messages.map(msg => `<div>${msg}</div>`).join('');
            }

            // Toggle form visibility
            toggleFormBtn.addEventListener('click', function() {
                resetBtn.classList.add('d-none');
                errorBox.classList.add('d-none');
                if (courseForm.style.display === 'none') {
                    courseForm.style.display = 'block';
                    toggleFormBtn.textContent = 'Hide Course Form';
                } else {
                    courseForm.style.display = 'none';
                    toggleFormBtn.textContent = 'Show Course Form';
                }
                resetForm();
            });

            // Load courses + teachers
            function loadCourses(search = '', author = '') {
                fetch(`{{ route('ajax-courses.index') }}?search=${encodeURIComponent(search)}&author=${author}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Populate dropdowns
                        authorSelect.innerHTML = '<option value="">Select Author (Teacher)</option>';
                        filterAuthor.innerHTML = '<option value="">All Authors</option>';
                        data.authors.forEach(t => {
                            const selected = (t.id == author) ? 'selected' : '';
                            authorSelect.innerHTML +=
                                `<option value="${t.id}" ${selected}>${t.name}</option>`;
                            filterAuthor.innerHTML +=
                                `<option value="${t.id}" ${selected}>${t.name}</option>`;
                        });

                        // Populate table
                        tableBody.innerHTML = '';
                        if (data.courses.length === 0) {
                            tableBody.innerHTML =
                                `<tr><td colspan="5" class="text-center text-muted">No courses found.</td></tr>`;
                            return;
                        }

                        data.courses.forEach(course => {
                            tableBody.innerHTML += `
                        <tr>
                            <td>${course.id}</td>
                            <td>${course.title}</td>
                            <td>${course.description.slice(0, 40)}</td>
                            <td><a href="/admin/user/${course.author.id}/show">${course.author.name}</a></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="editCourse(${course.id}, '${course.title}', \`${course.description}\`, ${course.author_id})">Edit</button>
                                <a href="/admin/course/${course.id}/show" class="btn btn-sm btn-secondary">View</a>
                                <button class="btn btn-sm btn-danger" onclick="deleteCourse(${course.id})">Delete</button>
                            </td>
                        </tr>`;
                        });
                    });
            }

            // --- Filtering events ---
            let searchTimer;
            searchInput.addEventListener('input', e => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    loadCourses(e.target.value.trim(), filterAuthor.value);
                }, 300);
            });

            filterAuthor.addEventListener('change', e => {
                loadCourses(searchInput.value.trim(), e.target.value);
            });

            // Edit course
            window.editCourse = function(id, title, description, author_id) {
                idInput.value = id;
                resetBtn.classList.remove('d-none');
                document.getElementById('title').value = title;
                document.getElementById('description').value = description;
                document.getElementById('author_id').value = author_id;
                addBtn.textContent = 'Update Course';
                courseForm.style.display = 'block';
                toggleFormBtn.textContent = 'Hide Course Form';
                showErrors(); // clear errors
            };

            // Reset form
            function resetForm() {
                idInput.value = '';
                resetBtn.classList.add('d-none');
                document.getElementById('title').value = '';
                document.getElementById('description').value = '';
                document.getElementById('author_id').value = '';
                addBtn.textContent = 'Add Course';
                showErrors(); // clear errors
            }
            resetBtn.addEventListener('click', function() {
                resetForm();
            });

            // Add or update course
            addBtn.addEventListener('click', function() {
                const title = document.getElementById('title').value.trim();
                const description = document.getElementById('description').value.trim();
                const author_id = document.getElementById('author_id').value;
                const course_id = idInput.value;

                const errors = [];
                if (!title) errors.push('Title is required.');
                if (!description) errors.push('Description is required.');
                if (!author_id) errors.push('Author is required.');

                if (errors.length) {
                    showErrors(errors);
                    return;
                }

                showErrors(); // clear old errors

                const method = course_id ? 'PUT' : 'POST';
                const url = course_id ?
                    `/admin/ajax-courses/${course_id}` :
                    `{{ route('ajax-courses.store') }}`;

                addBtn.disabled = true;
                addBtn.textContent = course_id ? 'Updating...' : 'Adding...';

                fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            title,
                            description,
                            author_id
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        addBtn.disabled = false;
                        addBtn.textContent = course_id ? 'Update Course' : 'Add Course';

                        if (data.success) {
                            loadCourses();
                            resetBtn.click();
                            if (window.toastr) toastr.success(course_id ? 'Course updated!' :
                                'Course added!');
                        } else {
                            const msg = data.message || 'Something went wrong!';
                            showErrors([msg]);
                            if (window.toastr) toastr.error(msg);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        addBtn.disabled = false;
                        addBtn.textContent = course_id ? 'Update Course' : 'Add Course';
                        showErrors(['A network or server error occurred.']);
                        if (window.toastr) toastr.error('A network or server error occurred.');
                    });
            });
            // Delete course
            window.deleteCourse = function(id) {
                if (window.confirm('Are you sure?')) {
                    fetch(`/admin/ajax-courses/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                loadCourses();
                                if (window.toastr) toastr.success('Course deleted!');
                            } else {
                                const msg = data.message || 'Something went wrong!';
                                showErrors([msg]);
                                if (window.toastr) toastr.error(msg);
                            }
                        });
                }
            }
            searchInput.addEventListener('input', e => loadCourses(e.target.value));
            loadCourses();
        });
    </script>
@endsection
