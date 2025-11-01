@extends(backpack_view('blank'))

@section('content')
<x-toastr />
<div class="container mt-4">
    <h3>AJAX Lessons CRUD</h3>

    <button id="toggleFormBtn" class="btn btn-primary mb-3">Show Lesson Form</button>

    <div id="lessonForm" style="display: none;" class="mb-3">
        <div id="errorBox" class="alert alert-danger d-none"></div>

        <input type="hidden" id="lesson_id">
        <input type="text" id="title" class="form-control mb-2" placeholder="Lesson Title">
        <textarea id="content" class="form-control mb-2" placeholder="Lesson Content"></textarea>
        <select id="course_id" class="form-control mb-2">
            <option value="">Select Course</option>
        </select>
        <div class="d-flex gap-2">
            <button id="addLesson" class="btn btn-success">Add Lesson</button>
            <button id="resetForm" type="button" class="btn btn-secondary d-none">Reset</button>
        </div>
    </div>

    <div class="d-flex mb-3 align-items-center gap-2">
        <input type="text" id="search" class="form-control" placeholder="Search by title or content..." style="max-width: 300px;">
        <select id="filterCourse" class="form-select" style="max-width: 250px;">
            <option value="">All Courses</option>
        </select>
    </div>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Course</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="lessonTable"></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const lessonForm = document.getElementById('lessonForm');
    const tableBody = document.getElementById('lessonTable');
    const paginationDiv = document.createElement('div');
    paginationDiv.classList.add('mt-3', 'd-flex', 'justify-content-center');
    lessonForm.parentElement.appendChild(paginationDiv);
    const searchInput = document.getElementById('search');
    const filterCourse = document.getElementById('filterCourse');
    const addBtn = document.getElementById('addLesson');
    const resetBtn = document.getElementById('resetForm');
    const idInput = document.getElementById('lesson_id');
    const errorBox = document.getElementById('errorBox');
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
        lessonForm.style.display = lessonForm.style.display === 'none' ? 'block' : 'none';
        toggleFormBtn.textContent = lessonForm.style.display === 'block' ? 'Hide Lesson Form' : 'Show Lesson Form';
    });

    // load lessons with pagination
    window.loadLessons = function(search = '', course = '', page = 1) {
        fetch(`{{ route('ajax-lesson.index') }}?search=${encodeURIComponent(search)}&course=${course}&page=${page}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';
            courseSelect.innerHTML = '<option value="">Select Course</option>';
            filterCourse.innerHTML = '<option value="">All Courses</option>';

            data.courses.forEach(c => {
                const selected = (c.id == course) ? 'selected' : '';
                courseSelect.innerHTML += `<option value="${c.id}" ${selected}>${c.title}</option>`;
                filterCourse.innerHTML += `<option value="${c.id}" ${selected}>${c.title}</option>`;
            });

            if (!data.lessons.length) {
                tableBody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No lessons found.</td></tr>`;
                paginationDiv.innerHTML = '';
                return;
            }

            data.lessons.forEach(l => {
                tableBody.innerHTML += `
                    <tr>
                        <td>${l.title}</td>
                        <td>${l.content.slice(0,40)}</td>
                        <td><a href="/admin/course/${l.course_id}/show">${l.course?.title || '-'}</a></td>
                        <td>
                            <a href="/admin/lesson/${l.id}/show" class="btn btn-sm btn-secondary">View</a>
                            <button class="btn btn-sm btn-info" onclick="editLesson(${l.id}, '${l.title}', \`${l.content}\`, ${l.course_id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteLesson(${l.id})">Delete</button>
                        </td>
                    </tr>`;
            });

            // Pagination buttons
            const { current_page, last_page } = data.pagination;
            paginationDiv.innerHTML = '';
            let paginationHTML = `<nav><ul class="pagination">`;
            for (let i = 1; i <= last_page; i++) {
                paginationHTML += `<li class="page-item ${i === current_page ? 'active' : ''}">
                    <button class="page-link" onclick="loadLessons('${searchInput.value.trim()}', '${filterCourse.value}', ${i})">${i}</button>
                </li>`;
            }
            paginationHTML += `</ul></nav>`;
            paginationDiv.innerHTML = paginationHTML;
        });
    }

    // edit lesson
    window.editLesson = (id, title, content, course_id) => {
        idInput.value = id;
        document.getElementById('title').value = title;
        document.getElementById('content').value = content;
        courseSelect.value = course_id;
        addBtn.textContent = 'Update Lesson';
        resetBtn.classList.remove('d-none');
        lessonForm.style.display = 'block';
        toggleFormBtn.textContent = 'Hide Lesson Form';
        showErrors();
    };

    function resetForm() {
        idInput.value = '';
        document.getElementById('title').value = '';
        document.getElementById('content').value = '';
        courseSelect.value = '';
        addBtn.textContent = 'Add Lesson';
        resetBtn.classList.add('d-none');
        showErrors();
    }

    resetBtn.addEventListener('click', resetForm);

    addBtn.addEventListener('click', () => {
        const title = document.getElementById('title').value.trim();
        const content = document.getElementById('content').value.trim();
        const course_id = courseSelect.value;
        const lesson_id = idInput.value;

        const errors = [];
        if (!title) errors.push('Title is required.');
        if (!content) errors.push('Content is required.');
        if (!course_id) errors.push('Course is required.');
        if (errors.length) return showErrors(errors);

        showErrors();

        const method = lesson_id ? 'PUT' : 'POST';
        const url = lesson_id ? `/admin/ajax-lesson/${lesson_id}` : `{{ route('ajax-lesson.store') }}`;

        addBtn.disabled = true;
        addBtn.textContent = lesson_id ? 'Updating...' : 'Adding...';

        fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ title, content, course_id })
        })
        .then(res => res.json())
        .then(data => {
            addBtn.disabled = false;
            addBtn.textContent = lesson_id ? 'Update Lesson' : 'Add Lesson';
            if (data.success) {
                loadLessons(searchInput.value, filterCourse.value);
                resetForm();
                if (window.toastr) toastr.success(lesson_id ? 'Lesson updated!' : 'Lesson added!');
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

    window.deleteLesson = id => {
        if (!confirm('Are you sure?')) return;
        fetch(`/admin/ajax-lesson/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadLessons(searchInput.value, filterCourse.value);
                if (window.toastr) toastr.success('Lesson deleted!');
            } else {
                showErrors([data.message || 'Something went wrong']);
            }
        });
    };

    let searchTimer;
    searchInput.addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadLessons(e.target.value.trim(), filterCourse.value), 300);
    });

    filterCourse.addEventListener('change', e => loadLessons(searchInput.value, e.target.value));

    loadLessons();
});
</script>

@endsection
