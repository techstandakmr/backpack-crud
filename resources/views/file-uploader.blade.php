@extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">File Text Extractor</h2>

        <form id="fileForm" class="card p-4 shadow-sm bg-white" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="file">Select PDF or Image</label>
                <input type="file" class="form-control-file" name="file" accept="application/pdf,image/*" required>
            </div>

            <div id="preview-container" class="mb-3" style="display:none;">
                <label>File Preview:</label>
                <iframe id="filePreview"
                    style="width:100%; height:300px; border:1px solid #ccc; border-radius:6px; display:none;"></iframe>
                <img id="imagePreview"
                    style="max-width:100%; max-height:300px; border:1px solid #ccc; border-radius:6px; display:none;" />
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Extract Text</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ms-2">Reset</button>
            </div>
            <div id="loading" class="mt-3 text-info" style="display:none;"> Extracting text, please wait...</div>
        </form>

        <div id="result" class="card mt-4 p-3 bg-white shadow-sm overflow-x-auto" style="display:none;">
            <h5>Extracted Data</h5>
            <table class="table table-bordered table-striped mt-2 text-dark" id="dataTable" style="display:none;">
                <thead>
                    <tr id="tableHeader"></tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
            <div id="rawText" class="mt-3 text-dark" style="white-space: pre-wrap;"></div>
            <div id="noData" class="text-muted">No structured data found.</div>
        </div>
    </div>

    <script>
        const fileInput = document.querySelector('input[name="file"]');
        const previewContainer = document.getElementById('preview-container');
        const filePreview = document.getElementById('filePreview');
        const imagePreview = document.getElementById('imagePreview');
        const resetBtn = document.getElementById('resetBtn');

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Only allow images and PDFs
            if (!file.type.startsWith('image/') && file.type !== 'application/pdf') {
                alert('Only PDF and image files are allowed.');
                fileInput.value = '';
                return;
            }

            const fileURL = URL.createObjectURL(file);

            if (file.type === 'application/pdf') {
                filePreview.src = fileURL;
                filePreview.style.display = 'block';
                imagePreview.style.display = 'none';
            } else if (file.type.startsWith('image/')) {
                imagePreview.src = fileURL;
                imagePreview.style.display = 'block';
                filePreview.style.display = 'none';
            }

            previewContainer.style.display = 'block';
        });

        resetBtn.addEventListener('click', function() {
            fileInput.value = '';
            previewContainer.style.display = 'none';
            filePreview.src = '';
            imagePreview.src = '';
            document.getElementById('loading').style.display = 'none';
            document.getElementById('result').style.display = 'none';
        });


        const renderTable = (data) => {
            if (data.table) {
                const table = document.getElementById('dataTable');
                const header = document.getElementById('tableHeader');
                const body = document.getElementById('tableBody');
                const rows = data.table;

                header.innerHTML = '';
                body.innerHTML = '';

                const keys = Object.keys(rows[0]);
                keys.forEach(k => {
                    const th = document.createElement('th');
                    th.textContent = k.replace(/_/g, ' ');
                    header.appendChild(th);
                });

                rows.forEach(row => {
                    const tr = document.createElement('tr');
                    keys.forEach(k => {
                        const td = document.createElement('td');
                        td.textContent = row[k];
                        tr.appendChild(td);
                    });
                    body.appendChild(tr);
                });

                table.style.display = 'table';
                document.getElementById('noData').style.display = 'none';
                return;
            }
        };

        document.getElementById('fileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            loading.style.display = 'block';
            result.style.display = 'none';

            try {
                const response = await fetch('{{ route('file.text.extract') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });

                const data = await response.json();
                loading.style.display = 'none';
                result.style.display = 'block';

                if (data.status === 'success') {
                    renderTable(data);
                    if (data.raw) {
    document.getElementById('rawText').innerText = data.raw;
}

                } else {
                    document.getElementById('noData').textContent = data.error || 'Extraction failed';
                }
            } catch (err) {
                loading.style.display = 'none';
                alert('Error: ' + err.message);
            }
        });
    </script>
@endsection
