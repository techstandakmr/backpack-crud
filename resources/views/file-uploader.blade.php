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
            <iframe id="filePreview" style="width:100%; height:300px; border:1px solid #ccc; border-radius:6px; display:none;"></iframe>
            <img id="imagePreview" style="max-width:100%; max-height:300px; border:1px solid #ccc; border-radius:6px; display:none;" />
        </div>
        <div>
            <button type="submit" class="btn btn-primary">Extract Text</button>
            <button type="button" id="resetBtn" class="btn btn-secondary ms-2">Reset</button>
        </div>
        <div id="loading" class="mt-3 text-info" style="display:none;"> Extracting text, please wait...</div>
    </form>

    <div id="result" class="card mt-4 p-3 bg-white shadow-sm" style="display:none; max-height:500px; overflow-y:auto;">
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

document.getElementById('fileForm').addEventListener('submit', async function(e){
    e.preventDefault();

    const formData = new FormData(e.target);
    const loading = document.getElementById('loading');
    const result = document.getElementById('result');
    const preResult = document.getElementById('preResult');

    loading.style.display = 'block';
    result.style.display = 'none';
    preResult.textContent = '';

    try {
        const response = await fetch('{{ route("file.text.extract") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        });

        const data = await response.json();
        loading.style.display = 'none';
        result.style.display = 'block';

        if (data.status === 'success') {
            preResult.textContent = data.text;
        } else {
            preResult.textContent = 'Failed to extract text: ' + (data.error || '');
        }
    } catch (err) {
        loading.style.display = 'none';
        result.style.display = 'block';
        preResult.textContent = ' An error occurred: ' + err.message;
    }
});
</script>
@endsection
