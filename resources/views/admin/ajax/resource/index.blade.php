@extends(backpack_view('blank'))

@section('content')
<x-toastr />

<div class="container mt-4">
    <h3>AJAX Resources CRUD</h3>

    <button id="toggleFormBtn" class="btn btn-primary mb-3">Show Resource Form</button>

    {{-- Resource Form --}}
    <div id="resourceForm" style="display: none;" class="mb-3">
        <div id="errorBox" class="alert alert-danger d-none"></div>

        <input type="hidden" id="resource_id">
        <input type="text" id="name" class="form-control mb-2" placeholder="Resource Name">
        <input type="text" id="url" class="form-control mb-2" placeholder="Resource URL">
        <select id="lesson_id" class="form-control mb-2">
            <option value="">Select Lesson</option>
        </select>

        <div class="d-flex gap-2">
            <button id="addResource" class="btn btn-success">Add Resource</button>
            <button id="resetForm" type="button" class="btn btn-secondary d-none">Reset</button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="d-flex mb-3 align-items-center gap-2">
        <input type="text" id="search" class="form-control" placeholder="Search by name, URL, or lesson..." style="max-width: 300px;">
        <select id="filterLesson" class="form-select" style="max-width: 250px;">
            <option value="">All Lessons</option>
        </select>
    </div>

    {{-- Table --}}
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>Name</th>
                <th>URL</th>
                <th>Lesson</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="resourceTable"></tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleFormBtn = document.getElementById('toggleFormBtn');
    const resourceForm = document.getElementById('resourceForm');
    const tableBody = document.getElementById('resourceTable');
    const paginationDiv = document.createElement('div');
    paginationDiv.classList.add('mt-3', 'd-flex', 'justify-content-center');
    resourceForm.parentElement.appendChild(paginationDiv);

    const searchInput = document.getElementById('search');
    const filterLesson = document.getElementById('filterLesson');
    const addBtn = document.getElementById('addResource');
    const resetBtn = document.getElementById('resetForm');
    const idInput = document.getElementById('resource_id');
    const errorBox = document.getElementById('errorBox');
    const lessonSelect = document.getElementById('lesson_id');

    function showErrors(messages = []) {
        if (!messages.length) {
            errorBox.classList.add('d-none');
            errorBox.innerHTML = '';
            return;
        }
        errorBox.classList.remove('d-none');
        errorBox.innerHTML = messages.map(msg => `<div>${msg}</div>`).join('');
    }

    // toggle form
    toggleFormBtn.addEventListener('click', () => {
        resetForm();
        resourceForm.style.display = resourceForm.style.display === 'none' ? 'block' : 'none';
        toggleFormBtn.textContent = resourceForm.style.display === 'block' ? 'Hide Resource Form' : 'Show Resource Form';
    });

    // Load resources with pagination
    window.loadResources = function(search = '', lesson = '', page = 1) {
        fetch(`{{ route('ajax-resource.index') }}?search=${encodeURIComponent(search)}&lesson=${lesson}&page=${page}`, {
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        })
        .then(res => res.json())
        .then(data => {
            tableBody.innerHTML = '';
            lessonSelect.innerHTML = '<option value="">Select Lesson</option>';
            filterLesson.innerHTML = '<option value="">All Lessons</option>';

            data.lessons.forEach(l => {
                const selected = (l.id == lesson) ? 'selected' : '';
                lessonSelect.innerHTML += `<option value="${l.id}" ${selected}>${l.title}</option>`;
                filterLesson.innerHTML += `<option value="${l.id}" ${selected}>${l.title}</option>`;
            });

            if (!data.resources.length) {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center text-muted">No resources found.</td></tr>`;
                paginationDiv.innerHTML = '';
                return;
            }

            data.resources.forEach(r => {
                tableBody.innerHTML += `
                    <tr>
                        <td>${r.name}</td>
                        <td><a href="${r.url}" target="_blank">${r.url}</a></td>
                        <td><a href="/admin/lesson/${r.lesson_id}/show">${r.lesson?.title || '-'}</a></td>
                        <td>
                            <a href="/admin/resource/${r.id}/show" class="btn btn-sm btn-secondary">View</a>
                            <button class="btn btn-sm btn-info" onclick="editResource(${r.id}, '${r.name}', '${r.url}', ${r.lesson_id})">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteResource(${r.id})">Delete</button>
                        </td>
                    </tr>`;
            });

            // Pagination
            const { current_page, last_page } = data.pagination;
            paginationDiv.innerHTML = '';
            let paginationHTML = `<nav><ul class="pagination">`;
            for (let i = 1; i <= last_page; i++) {
                paginationHTML += `<li class="page-item ${i === current_page ? 'active' : ''}">
                    <button class="page-link" onclick="loadResources('${searchInput.value.trim()}', '${filterLesson.value}', ${i})">${i}</button>
                </li>`;
            }
            paginationHTML += `</ul></nav>`;
            paginationDiv.innerHTML = paginationHTML;
        });
    };

    // edit
    window.editResource = (id, name, url, lesson_id) => {
        idInput.value = id;
        document.getElementById('name').value = name;
        document.getElementById('url').value = url;
        lessonSelect.value = lesson_id;
        addBtn.textContent = 'Update Resource';
        resetBtn.classList.remove('d-none');
        resourceForm.style.display = 'block';
        toggleFormBtn.textContent = 'Hide Resource Form';
        showErrors();
    };

    function resetForm() {
        idInput.value = '';
        document.getElementById('name').value = '';
        document.getElementById('url').value = '';
        lessonSelect.value = '';
        addBtn.textContent = 'Add Resource';
        resetBtn.classList.add('d-none');
        showErrors();
    }

    resetBtn.addEventListener('click', resetForm);

    // Create or Update Resource
    addBtn.addEventListener('click', () => {
        const name = document.getElementById('name').value.trim();
        const url = document.getElementById('url').value.trim();
        const lesson_id = lessonSelect.value;
        const resource_id = idInput.value;

        const errors = [];
        if (!name) errors.push('Name is required.');
        if (!url) errors.push('URL is required.');
        if (!lesson_id) errors.push('Lesson is required.');
        if (errors.length) return showErrors(errors);

        showErrors();

        const method = resource_id ? 'PUT' : 'POST';
        const endpoint = resource_id ? `/admin/ajax-resource/${resource_id}` : `{{ route('ajax-resource.store') }}`;

        addBtn.disabled = true;
        addBtn.textContent = resource_id ? 'Updating...' : 'Adding...';

        fetch(endpoint, {
            method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ name, url, lesson_id })
        })
        .then(res => res.json())
        .then(data => {
            addBtn.disabled = false;
            addBtn.textContent = resource_id ? 'Update Resource' : 'Add Resource';
            if (data.success) {
                loadResources(searchInput.value, filterLesson.value);
                resetForm();
                if (window.toastr) toastr.success(resource_id ? 'Resource updated!' : 'Resource added!');
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
    window.deleteResource = id => {
        if (!confirm('Are you sure?')) return;
        fetch(`/admin/ajax-resource/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                loadResources(searchInput.value, filterLesson.value);
                if (window.toastr) toastr.success('Resource deleted!');
            } else {
                showErrors([data.message || 'Something went wrong']);
            }
        });
    };

    // Search + Filter
    let searchTimer;
    searchInput.addEventListener('input', e => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadResources(e.target.value.trim(), filterLesson.value), 300);
    });

    filterLesson.addEventListener('change', e => loadResources(searchInput.value, e.target.value));

    loadResources();
});
</script>
@endsection
