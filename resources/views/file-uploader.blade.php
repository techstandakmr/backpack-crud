{{--  table based --}}
{{-- @extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">📄 File Text Extractor</h2>

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
            <div id="loading" class="mt-3 text-info" style="display:none;">⏳ Extracting text, please wait...</div>
        </form>

        <div id="result" class="card mt-4 p-3 bg-white shadow-sm"
            style="display:none; max-height:500px; overflow-y:auto;">
            <pre id="preResult" style="white-space: pre-wrap;"></pre>
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
                alert('❌ Only PDF and image files are allowed.');
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
            const preResult = document.getElementById('preResult');

            loading.style.display = 'block';
            result.style.display = 'none';
            preResult.textContent = '';

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
                console.log(data);
                loading.style.display = 'none';
                result.style.display = 'block';

                if (data.status === 'success') {
                    preResult.textContent = data.text;
                } else {
                    preResult.textContent = '❌ Failed to extract text: ' + (data.error || '');
                }
            } catch (err) {
                loading.style.display = 'none';
                result.style.display = 'block';
                preResult.textContent = '❌ An error occurred: ' + err.message;
            }
        });
    </script>
@endsection --}}



{{-- table based --}}

@extends(backpack_view('blank'))

@section('content')
    <div class="container mt-4">
        <h2 class="mb-4">📄 PDF Table Extractor</h2>

        <form id="fileForm" class="card p-4 shadow-sm bg-white" enctype="multipart/form-data">
            <div class="form-group mb-3">
                <label for="file">Select PDF File</label>
                <input type="file" class="form-control-file" name="file" accept="application/pdf" required>
            </div>

            <div id="preview-container" class="mb-3" style="display:none;">
                <label>File Preview:</label>
                <iframe id="filePreview"
                    style="width:100%; height:300px; border:1px solid #ccc; border-radius:6px;"></iframe>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary">Extract Tables</button>
                <button type="button" id="resetBtn" class="btn btn-secondary ms-2">Reset</button>
                <button type="button" id="downloadBtn" class="btn btn-success ms-2" style="display:none;">Download as CSV</button>
            </div>
            
            <div id="loading" class="mt-3 text-info" style="display:none;">
                <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                ⏳ Extracting tables, please wait...
            </div>
        </form>

        <div id="result" class="card mt-4 p-3 bg-white shadow-sm" style="display:none;">
            <div id="resultContent"></div>
        </div>
    </div>

    <style>
        .table-container {
            margin-bottom: 30px;
        }
        .table-responsive {
            max-height: 500px;
            overflow-y: auto;
        }
        #resultContent table {
            font-size: 0.9rem;
        }
        #resultContent th {
            position: sticky;
            top: 0;
            background-color: #343a40 !important;
            color: white;
            z-index: 10;
        }
    </style>

    <script>
        const fileInput = document.querySelector('input[name="file"]');
        const previewContainer = document.getElementById('preview-container');
        const filePreview = document.getElementById('filePreview');
        const resetBtn = document.getElementById('resetBtn');
        const downloadBtn = document.getElementById('downloadBtn');
        let extractedData = null;

        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            // Only allow PDFs
            if (file.type !== 'application/pdf') {
                alert('❌ Only PDF files are allowed.');
                fileInput.value = '';
                return;
            }

            const fileURL = URL.createObjectURL(file);
            filePreview.src = fileURL;
            previewContainer.style.display = 'block';
        });

        resetBtn.addEventListener('click', function() {
            fileInput.value = '';
            previewContainer.style.display = 'none';
            filePreview.src = '';
            document.getElementById('loading').style.display = 'none';
            const result = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');
            result.style.display = 'none';
            resultContent.innerHTML = '';
            downloadBtn.style.display = 'none';
            extractedData = null;
        });

        downloadBtn.addEventListener('click', function() {
            if (!extractedData || !extractedData.tables) return;

            // Convert tables to CSV
            let csvContent = '';
            
            extractedData.tables.forEach((table, index) => {
                csvContent += `\nTable ${index + 1} (Page ${table.page})\n`;
                
                // Headers
                csvContent += table.headers.join(',') + '\n';
                
                // Data rows
                table.data.forEach(row => {
                    const values = table.headers.map(header => {
                        const value = row[header] || '';
                        // Escape commas and quotes
                        return `"${value.replace(/"/g, '""')}"`;
                    });
                    csvContent += values.join(',') + '\n';
                });
                
                csvContent += '\n';
            });

            // Download
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'extracted_tables.csv';
            link.click();
        });

        document.getElementById('fileForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const resultContent = document.getElementById('resultContent');

            loading.style.display = 'block';
            result.style.display = 'none';
            resultContent.innerHTML = '';
            downloadBtn.style.display = 'none';

            try {
                const response = await fetch('{{ route('file.text.extract') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                });

                const data = await response.json();
                console.log(data);
                
                loading.style.display = 'none';
                result.style.display = 'block';

                if (data.status === 'success') {
                    extractedData = data;
                    
                    // Display summary
                    let summaryHtml = `<div class="alert alert-success">
                        ✅ Successfully extracted ${data.total_tables} table(s) from the PDF
                    </div>`;
                    
                    // Display tables
                    resultContent.innerHTML = summaryHtml + data.html;
                    
                    // Show download button if we have data
                    if (data.total_tables > 0) {
                        downloadBtn.style.display = 'inline-block';
                    }
                } else {
                    resultContent.innerHTML = `<div class="alert alert-danger">
                        ❌ Failed to extract tables: ${data.error || 'Unknown error'}
                        ${data.raw_output ? '<br><br><pre>' + data.raw_output + '</pre>' : ''}
                    </div>`;
                }
            } catch (err) {
                loading.style.display = 'none';
                result.style.display = 'block';
                resultContent.innerHTML = `<div class="alert alert-danger">
                    ❌ An error occurred: ${err.message}
                </div>`;
            }
        });
    </script>
@endsection