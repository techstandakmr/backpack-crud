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

        <div id="result" class="card mt-4 p-3 bg-white shadow-sm"
            style="display:none; max-height:500px; overflow-y:auto;">
            <h5>Extracted Data</h5>
            <table class="table table-bordered table-striped mt-2" id="dataTable" style="display:none;">
                <thead>
                    <tr id="tableHeader"></tr>
                </thead>
                <tbody id="tableBody"></tbody>
            </table>
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
            const result = document.getElementById('result');
            const preResult = document.getElementById('preResult');
            result.style.display = 'none';
            preResult.textContent = '';
        });

        document.getElementById('fileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const tableHeader = document.getElementById('tableHeader');
            const tableBody = document.getElementById('tableBody');
            const dataTable = document.getElementById('dataTable');
            const noData = document.getElementById('noData');

            loading.style.display = 'block';
            result.style.display = 'none';
            dataTable.style.display = 'none';
            tableHeader.innerHTML = '';
            tableBody.innerHTML = '';
            noData.style.display = 'none';

            try {
                const response = await fetch('{{ route('file.text.extract') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: formData
                });

                const data = await response.json();
                console.log(data)
                loading.style.display = 'none';
                result.style.display = 'block';

                if (data.status === 'success' && data.tables.length > 0) {
                    data.tables.forEach((table, tIndex) => {
                        // If first table, set header
                        if (tIndex === 0 && table.length > 0) {
                            tableHeader.innerHTML = '';
                            table[0].forEach(header => {
                                const th = document.createElement('th');
                                th.textContent = header;
                                tableHeader.appendChild(th);
                            });
                        }

                        // Add rows
                        table.forEach((row, rIndex) => {
                            const tr = document.createElement('tr');
                            row.forEach(cell => {
                                const td = document.createElement('td');
                                td.textContent = cell;
                                tr.appendChild(td);
                            });
                            tableBody.appendChild(tr);
                        });
                    });

                    dataTable.style.display = 'table';
                } else {
                    noData.style.display = 'block';
                }

            } catch (err) {
                loading.style.display = 'none';
                result.style.display = 'block';
                noData.textContent = 'An error occurred: ' + err.message;
                noData.style.display = 'block';
            }
        });
    </script>
@endsection
