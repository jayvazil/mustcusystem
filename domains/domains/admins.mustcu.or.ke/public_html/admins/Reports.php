<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Management System</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/resized_image_1.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #0207ba;
            --primary-orange: #ff7900;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: #0205a6;
            border-color: #0205a6;
        }
        
        .btn-secondary {
            background-color: var(--primary-orange);
            border-color: var(--primary-orange);
        }
        
        .btn-secondary:hover {
            background-color: #e66d00;
            border-color: #e66d00;
        }
        
        .folder-icon {
            color: var(--primary-orange);
            font-size: 3rem;
        }
        
        .folder-item {
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .folder-item:hover {
            transform: translateY(-5px);
        }
        
        .breadcrumb-item.active {
            color: var(--primary-blue);
        }
        
        .file-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .file-type-pdf { color: #dc3545; }
        .file-type-doc { color: #0d6efd; }
        .file-type-img { color: var(--primary-orange); }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div id="files-page">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#" style="color: var(--primary-blue)">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Current Folder</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>File Management</h2>
                <div>
                    <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                        <i class="fas fa-folder-plus me-2"></i>New Folder
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal">
                        <i class="fas fa-upload me-2"></i>Upload Files
                    </button>
                </div>
            </div>

            <!-- Folders Grid -->
            <div class="row row-cols-1 row-cols-md-4 g-4 mb-4">
                <!-- Sample Folders -->
                <div class="col">
                    <div class="card h-100 folder-item">
                        <div class="card-body text-center">
                            <i class="fas fa-folder folder-icon mb-3"></i>
                            <h5 class="card-title">Documents</h5>
                            <small class="text-muted">3 files</small>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card h-100 folder-item">
                        <div class="card-body text-center">
                            <i class="fas fa-folder folder-icon mb-3"></i>
                            <h5 class="card-title">Images</h5>
                            <small class="text-muted">12 files</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Files Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="filesTable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Sample Files -->
                                <tr>
                                    <td>
                                        <i class="far fa-file-pdf text-danger me-2"></i>
                                        Report.pdf
                                    </td>
                                    <td>PDF</td>
                                    <td>2.5 MB</td>
                                    <td>2024-02-20</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary me-1"><i class="fas fa-download"></i></button>
                                        <button class="btn btn-sm btn-secondary"><i class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upload File Modal -->
        <div class="modal fade" id="uploadFileModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Files</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="uploadFileForm">
                            <div class="mb-3">
                                <label class="form-label">Select Files</label>
                                <input type="file" class="form-control" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <small class="text-muted">Supported formats: PDF, Word, Images</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Destination Folder</label>
                                <select class="form-select">
                                    <option value="root">Root Directory</option>
                                    <option value="documents">Documents</option>
                                    <option value="images">Images</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Folder Modal -->
        <div class="modal fade" id="createFolderModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Folder</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="createFolderForm">
                            <div class="mb-3">
                                <label class="form-label">Folder Name</label>
                                <input type="text" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-secondary">Create Folder</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
</body>
<script>
   // Global state for current path
let currentPath = {
    id: 1, // Root folder ID
    breadcrumb: [] // Array of {id, name} objects for navigation
};

// Utility functions
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (fileType) => {
    const icons = {
        'pdf': '<i class="far fa-file-pdf text-danger"></i>',
        'doc': '<i class="far fa-file-word text-primary"></i>',
        'docx': '<i class="far fa-file-word text-primary"></i>',
        'jpg': '<i class="far fa-file-image text-warning"></i>',
        'jpeg': '<i class="far fa-file-image text-warning"></i>',
        'png': '<i class="far fa-file-image text-warning"></i>'
    };
    return icons[fileType.toLowerCase()] || '<i class="far fa-file"></i>';
};

// API Functions
const fetchFolderContents = async (folderId) => {
    try {
        const response = await fetch(`api/folder-contents.php?folder_id=${folderId}`);
        const data = await response.json();
        
        if (data.success) {
            renderFolders(data.data.folders);
            renderFiles(data.data.files);
        } else {
            alert('Error loading folder contents: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to load folder contents');
    }
};

// Update breadcrumb navigation
const updateBreadcrumb = () => {
    const breadcrumb = document.querySelector('.breadcrumb');
    breadcrumb.innerHTML = `
        <li class="breadcrumb-item">
            <a href="#" data-folder-id="1" style="color: #0207ba">Home</a>
        </li>
    `;
    
    currentPath.breadcrumb.forEach((item, index) => {
        const isLast = index === currentPath.breadcrumb.length - 1;
        if (isLast) {
            breadcrumb.innerHTML += `
                <li class="breadcrumb-item active">${item.name}</li>
            `;
        } else {
            breadcrumb.innerHTML += `
                <li class="breadcrumb-item">
                    <a href="#" data-folder-id="${item.id}" style="color: #0207ba">
                        ${item.name}
                    </a>
                </li>
            `;
        }
    });
};

// Render folders grid
const renderFolders = (folders) => {
    const foldersGrid = document.querySelector('.row-cols-md-4');
    foldersGrid.innerHTML = '';

    folders.forEach(folder => {
        foldersGrid.innerHTML += `
            <div class="col">
                <div class="card h-100 folder-item" data-folder-id="${folder.id}">
                    <div class="card-body text-center">
                        <i class="fas fa-folder folder-icon mb-3"></i>
                        <h5 class="card-title">${folder.name}</h5>
                        <small class="text-muted">Created: ${new Date(folder.created_at).toLocaleDateString()}</small>
                    </div>
                </div>
            </div>
        `;
    });
};

// Render files table
const renderFiles = (files) => {
    const filesTableBody = document.querySelector('#filesTable tbody');
    filesTableBody.innerHTML = '';

    files.forEach(file => {
        filesTableBody.innerHTML += `
            <tr>
                <td>
                    ${getFileIcon(file.file_type)} ${file.original_name}
                </td>
                <td>${file.file_type.toUpperCase()}</td>
                <td>${formatFileSize(file.file_size)}</td>
                <td>${new Date(file.updated_at).toLocaleDateString()}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-1" onclick="downloadFile(${file.id})">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="deleteFile(${file.id}, ${currentPath.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
};

// Event Handlers
document.addEventListener('DOMContentLoaded', () => {
    // Initial load of root folder
    fetchFolderContents(1);

    // Handle folder navigation
    document.querySelector('.row-cols-md-4').addEventListener('click', async (e) => {
        const folderItem = e.target.closest('.folder-item');
        if (!folderItem) return;

        const folderId = folderItem.dataset.folderId;
        const folderName = folderItem.querySelector('.card-title').textContent;
        
        currentPath.id = folderId;
        currentPath.breadcrumb.push({ id: folderId, name: folderName });
        
        updateBreadcrumb();
        await fetchFolderContents(folderId);
    });

    // Handle breadcrumb navigation
    document.querySelector('.breadcrumb').addEventListener('click', async (e) => {
        e.preventDefault();
        const link = e.target.closest('[data-folder-id]');
        if (!link) return;

        const folderId = link.dataset.folderId;
        if (folderId === '1') {
            currentPath.id = 1;
            currentPath.breadcrumb = [];
        } else {
            const index = currentPath.breadcrumb.findIndex(item => item.id === folderId);
            currentPath.id = folderId;
            currentPath.breadcrumb = currentPath.breadcrumb.slice(0, index + 1);
        }

        updateBreadcrumb();
        await fetchFolderContents(folderId);
    });

    // Handle file upload
    document.getElementById('uploadFileForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('folder_id', currentPath.id);

        try {
            const response = await fetch('api/upload-file.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('uploadFileModal'));
                modal.hide();
                e.target.reset();
                await fetchFolderContents(currentPath.id);
            } else {
                alert('Error uploading file: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to upload file');
        }
    });

    // Handle folder creation
    document.getElementById('createFolderForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const folderName = e.target.querySelector('input').value;

        try {
            const response = await fetch('api/create-folder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: folderName,
                    parent_id: currentPath.id
                })
            });
            const data = await response.json();
            
            if (data.success) {
                const modal = bootstrap.Modal.getInstance(document.getElementById('createFolderModal'));
                modal.hide();
                e.target.reset();
                await fetchFolderContents(currentPath.id);
            } else {
                alert('Error creating folder: ' + data.error);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to create folder');
        }
    });
});

// File operations
const downloadFile = async (fileId) => {
    try {
        window.location.href = `api/download-file.php?file_id=${fileId}`;
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to download file');
    }
};

const deleteFile = async (fileId, folderId) => {
    if (!confirm('Are you sure you want to delete this file?')) return;

    try {
        const response = await fetch('api/delete-file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                file_id: fileId
            })
        });
        const data = await response.json();
        
        if (data.success) {
            await fetchFolderContents(folderId);
        } else {
            alert('Error deleting file: ' + data.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to delete file');
    }
};
</script>
</html>