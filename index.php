<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gambar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .upload-container {
            max-width: 600px;
            margin: 0 auto;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        #preview {
            max-width: 100%;
            max-height: 300px;
            margin-top: 1rem;
            display: none;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="upload-container">
        <h2 class="text-center mb-4">
            <i class="fas fa-cloud-upload-alt me-2"></i>Upload Gambar
        </h2>
        
        <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
            <div class="mb-4">
                <div class="upload-area" id="uploadArea">
                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                    <h5>Seret dan lepas gambar di sini</h5>
                    <p class="text-muted">atau</p>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('file').click()">
                        Pilih File
                    </button>
                    <input type="file" name="file" id="file" class="d-none" accept="image/*" required>
                </div>
                <img id="preview" class="img-fluid rounded">
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Deskripsi (Opsional)</label>
                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
            </div>
            
            <div class="d-grid">
                <button type="submit" class="btn btn-success btn-lg" id="submitBtn" disabled>
                    <i class="fas fa-upload me-1"></i> Upload Gambar
                </button>
            </div>
        </form>
        
        <div class="text-center mt-4">
            <a href="galeri_bootstrap3.php" class="btn btn-outline-secondary">
                <i class="fas fa-images me-1"></i> Lihat Galeri
            </a>
        </div>
    </div>
</div>

<script>
    // Preview gambar sebelum upload
    document.getElementById('file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.getElementById('preview');
                preview.src = event.target.result;
                preview.style.display = 'block';
                
                // Update upload area text
                document.getElementById('uploadArea').innerHTML = `
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>${file.name}</h5>
                    <p class="text-muted">${(file.size / 1024).toFixed(2)} KB</p>
                `;
                
                // Enable submit button
                document.getElementById('submitBtn').disabled = false;
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');
    
    uploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadArea.classList.add('border-primary');
        uploadArea.style.backgroundColor = '#f8f9fa';
    });
    
    uploadArea.addEventListener('dragleave', () => {
        uploadArea.classList.remove('border-primary');
        uploadArea.style.backgroundColor = '';
    });
    
    uploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadArea.classList.remove('border-primary');
        uploadArea.style.backgroundColor = '';
        
        const fileInput = document.getElementById('file');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            const event = new Event('change');
            fileInput.dispatchEvent(event);
        }
    });
</script>
</body>
</html>