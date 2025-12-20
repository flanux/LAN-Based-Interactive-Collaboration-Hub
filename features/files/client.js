// features/files/client.js - Client-side file handling

class FilesClient {
    constructor(app) {
        this.app = app;
        this.files = [];
        this.init();
    }
    
    init() {
        // Set up event listeners
        this.setupEventListeners();
        
        // Load existing files
        this.loadFiles();
        
        // Listen for file events
        this.app.on('file_uploaded', (data) => {
            this.onFileUploaded(data);
        });
        
        this.app.on('file_deleted', (data) => {
            this.onFileDeleted(data);
        });
    }
    
    setupEventListeners() {
        const dropZone = document.getElementById('fileDropZone');
        const fileInput = document.getElementById('fileInput');
        const browseBtn = document.getElementById('browseBtn');
        
        if (!dropZone || !fileInput || !browseBtn) return;
        
        // Browse button
        browseBtn.addEventListener('click', () => {
            fileInput.click();
        });
        
        // File input change
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                this.uploadFiles(Array.from(e.target.files));
            }
        });
        
        // Drag and drop
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('drag-over');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length > 0) {
                this.uploadFiles(Array.from(e.dataTransfer.files));
            }
        });
    }
    
    async loadFiles() {
        try {
            const response = await fetch(`api.php?action=get_files&roomId=${this.app.roomId}`);
            const data = await response.json();
            
            if (data.success) {
                this.files = data.files;
                this.renderFiles();
            }
        } catch (error) {
            console.error('Error loading files:', error);
        }
    }
    
    async uploadFiles(files) {
        const uploadStatus = document.getElementById('uploadStatus');
        
        for (const file of files) {
            // Check file size before uploading
            const maxSize = 2048 * 1024 * 1024; // 2GB
            if (file.size > maxSize) {
                if (uploadStatus) {
                    uploadStatus.textContent = `Error: ${file.name} is too large (max 2GB)`;
                    uploadStatus.style.background = '#ffebee';
                    uploadStatus.style.borderColor = '#e74c3c';
                    uploadStatus.style.color = '#c0392b';
                    uploadStatus.style.display = 'block';
                }
                continue;
            }
            
            if (uploadStatus) {
                const sizeMB = (file.size / 1024 / 1024).toFixed(1);
                uploadStatus.textContent = `Uploading ${file.name} (${sizeMB} MB)... Large files may take several minutes.`;
                uploadStatus.style.background = '#d5f4e6';
                uploadStatus.style.borderColor = '#2ecc71';
                uploadStatus.style.color = '#27ae60';
                uploadStatus.style.display = 'block';
            }
            
            const formData = new FormData();
            formData.append('action', 'upload_file');
            formData.append('roomId', this.app.roomId);
            formData.append('username', this.app.username);
            formData.append('file', file);
            
            try {
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    console.log('File uploaded:', data.file);
                    if (uploadStatus) {
                        uploadStatus.textContent = `${file.name} uploaded successfully!`;
                        uploadStatus.style.background = '#d5f4e6';
                        uploadStatus.style.borderColor = '#2ecc71';
                        uploadStatus.style.color = '#27ae60';
                        setTimeout(() => {
                            uploadStatus.style.display = 'none';
                        }, 3000);
                    }
                } else {
                    console.error('Upload failed:', data.error);
                    if (uploadStatus) {
                        uploadStatus.textContent = `Error: ${data.error}`;
                        uploadStatus.style.background = '#ffebee';
                        uploadStatus.style.borderColor = '#e74c3c';
                        uploadStatus.style.color = '#c0392b';
                    }
                }
            } catch (error) {
                console.error('Upload error:', error);
                if (uploadStatus) {
                    uploadStatus.textContent = `Error uploading ${file.name}: ${error.message}`;
                    uploadStatus.style.background = '#ffebee';
                    uploadStatus.style.borderColor = '#e74c3c';
                    uploadStatus.style.color = '#c0392b';
                }
            }
        }
    }
    
    onFileUploaded(fileInfo) {
        console.log('New file uploaded:', fileInfo);
        this.files.push(fileInfo);
        this.renderFiles();
        
        // Show notification
        this.app.log(`${fileInfo.uploadedBy} uploaded: ${fileInfo.originalName}`);
    }
    
    onFileDeleted(data) {
        console.log('File deleted:', data);
        this.files = this.files.filter(f => f.id !== data.fileId);
        this.renderFiles();
        
        this.app.log(`${data.deletedBy} deleted: ${data.fileName}`);
    }
    
    renderFiles() {
        const filesList = document.getElementById('filesList');
        if (!filesList) return;
        
        if (this.files.length === 0) {
            filesList.innerHTML = '<div class="no-files">No files shared yet</div>';
            return;
        }
        
        filesList.innerHTML = '';
        
        this.files.forEach(file => {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const fileIcon = document.createElement('div');
            fileIcon.className = 'file-icon';
            fileIcon.textContent = this.getFileIcon(file.originalName);
            
            const fileInfo = document.createElement('div');
            fileInfo.className = 'file-info';
            
            const fileName = document.createElement('div');
            fileName.className = 'file-name';
            fileName.textContent = file.originalName;
            
            const fileMeta = document.createElement('div');
            fileMeta.className = 'file-meta';
            fileMeta.textContent = `${this.formatFileSize(file.size)} • ${file.uploadedBy} • ${this.formatTime(file.uploadedAt)}`;
            
            fileInfo.appendChild(fileName);
            fileInfo.appendChild(fileMeta);
            
            const fileActions = document.createElement('div');
            fileActions.className = 'file-actions';
            
            const downloadBtn = document.createElement('button');
            downloadBtn.className = 'btn-download';
            downloadBtn.textContent = 'Download';
            downloadBtn.onclick = () => this.downloadFile(file);
            
            fileActions.appendChild(downloadBtn);
            
            // Only show delete button for file uploader or teacher
            if (file.uploadedBy === this.app.username || this.app.role === 'teacher') {
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'btn-delete';
                deleteBtn.textContent = 'Delete';
                deleteBtn.onclick = () => this.deleteFile(file);
                fileActions.appendChild(deleteBtn);
            }
            
            fileItem.appendChild(fileIcon);
            fileItem.appendChild(fileInfo);
            fileItem.appendChild(fileActions);
            
            filesList.appendChild(fileItem);
        });
    }
    
    downloadFile(file) {
        // Simple approach: just open the download URL directly
        // This avoids fetch/blob issues and lets the browser handle it
        const url = `api.php?action=download_file&roomId=${this.app.roomId}&fileId=${file.id}`;
        
        // Create a hidden link and click it
        const link = document.createElement('a');
        link.href = url;
        link.download = file.originalName;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        
        // Clean up after a short delay
        setTimeout(() => {
            document.body.removeChild(link);
        }, 100);
        
        console.log('Downloading:', file.originalName);
    }
    
    async deleteFile(file) {
        if (!confirm(`Delete ${file.originalName}?`)) return;
        
        const formData = new FormData();
        formData.append('action', 'delete_file');
        formData.append('roomId', this.app.roomId);
        formData.append('fileId', file.id);
        formData.append('username', this.app.username);
        
        try {
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!data.success) {
                alert('Error deleting file: ' + data.error);
            }
        } catch (error) {
            console.error('Delete error:', error);
            alert('Error deleting file');
        }
    }
    
    getFileIcon(fileName) {
        const ext = fileName.split('.').pop().toLowerCase();
        
        const icons = {
            'pdf': '📄',
            'doc': '📝', 'docx': '📝',
            'xls': '📊', 'xlsx': '📊',
            'ppt': '📊', 'pptx': '📊',
            'jpg': '🖼️', 'jpeg': '🖼️', 'png': '🖼️', 'gif': '🖼️',
            'mp4': '🎥', 'avi': '🎥', 'mov': '🎥',
            'mp3': '🎵', 'wav': '🎵',
            'zip': '📦', 'rar': '📦',
            'txt': '📃',
        };
        
        return icons[ext] || '📎';
    }
    
    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }
    
    formatTime(timestamp) {
        const date = new Date(timestamp * 1000);
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 1) return 'just now';
        if (diffMins < 60) return diffMins + ' min ago';
        if (diffMins < 1440) return Math.floor(diffMins / 60) + ' hr ago';
        return date.toLocaleDateString();
    }
}