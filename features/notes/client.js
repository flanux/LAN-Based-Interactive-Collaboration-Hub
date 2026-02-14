class NotesClient {
    constructor(app) {
        this.app = app;
        this.textarea = null;
        this.saveTimeout = null;
        this.lastContent = '';
        this.init();
    }
    
    init() {
        this.textarea = document.getElementById('notesTextarea');
        if (!this.textarea) return;
        
        this.loadNotes();
        
        this.setupEventListeners();
        
        this.app.on('notes_updated', (data) => {
            this.onNotesUpdated(data);
        });
        
        this.app.on('notes_cleared', (data) => {
            this.onNotesCleared(data);
        });
        
        if (this.app.role !== 'teacher') {
            this.textarea.readOnly = true;
            this.textarea.placeholder = 'Teacher notes will appear here...';
            
            const clearBtn = document.getElementById('clearNotesBtn');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
        }
    }
    
    setupEventListeners() {
        if (this.app.role === 'teacher') {
            this.textarea.addEventListener('input', () => {
                this.autoSave();
            });
            
            this.textarea.addEventListener('blur', () => {
                this.saveNotes();
            });
            
            const clearBtn = document.getElementById('clearNotesBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    this.showClearNotesModal();
                });
            }
        }
        
        // Setup clear notes modal buttons
        const cancelClearBtn = document.getElementById('cancelClearBtn');
        const confirmClearBtn = document.getElementById('confirmClearBtn');
        
        if (cancelClearBtn) {
            cancelClearBtn.addEventListener('click', () => {
                this.hideClearNotesModal();
            });
        }
        
        if (confirmClearBtn) {
            confirmClearBtn.addEventListener('click', () => {
                this.confirmClearNotes();
            });
        }
    }
    
    async loadNotes() {
        try {
            const response = await fetch(`api.php?action=get_notes&roomId=${this.app.roomId}`);
            const data = await response.json();
            
            if (data.success) {
                this.lastContent = data.content;
                this.textarea.value = data.content;
                
                if (data.updatedBy && data.updatedAt) {
                    this.showUpdateInfo(data.updatedBy, data.updatedAt);
                }
            }
        } catch (error) {
            console.error('Error loading notes:', error);
        }
    }
    
    autoSave() {
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }
        
        this.saveTimeout = setTimeout(() => {
            this.saveNotes();
        }, 1000);
    }
    
    async saveNotes() {
        const content = this.textarea.value;
        
        if (content === this.lastContent) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'update_notes');
            formData.append('roomId', this.app.roomId);
            formData.append('content', content);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.lastContent = content;
                this.showSaveIndicator();
            } else {
                console.error('Failed to save notes:', data.error);
            }
        } catch (error) {
            console.error('Error saving notes:', error);
        }
    }
    
    showClearNotesModal() {
        const modal = document.getElementById('clearNotesModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }
    
    hideClearNotesModal() {
        const modal = document.getElementById('clearNotesModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    async confirmClearNotes() {
        this.hideClearNotesModal();
        
        try {
            const formData = new FormData();
            formData.append('action', 'clear_notes');
            formData.append('roomId', this.app.roomId);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.textarea.value = '';
                this.lastContent = '';
                this.showNotification('Notes cleared successfully', 'success');
            } else {
                this.showNotification('Error clearing notes: ' + data.error, 'error');
            }
        } catch (error) {
            console.error('Error clearing notes:', error);
            this.showNotification('Error clearing notes', 'error');
        }
    }
    
    async clearNotes() {
        if (!confirm('Clear all notes? This cannot be undone.')) return;
        
        try {
            const formData = new FormData();
            formData.append('action', 'clear_notes');
            formData.append('roomId', this.app.roomId);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.textarea.value = '';
                this.lastContent = '';
            } else {
                alert('Error clearing notes: ' + data.error);
            }
        } catch (error) {
            console.error('Error clearing notes:', error);
            alert('Error clearing notes');
        }
    }
    
    onNotesUpdated(data) {
        if (data.updatedBy === this.app.username) return;
        
        this.textarea.value = data.content;
        this.lastContent = data.content;
        
        this.showUpdateInfo(data.updatedBy, data.updatedAt);
        
        this.app.log(`Notes updated by ${data.updatedBy}`);
    }
    
    onNotesCleared(data) {
        this.textarea.value = '';
        this.lastContent = '';
        this.app.log(`Notes cleared by ${data.clearedBy}`);
    }
    
    showSaveIndicator() {
        const indicator = document.getElementById('saveIndicator');
        if (indicator) {
            indicator.textContent = '✓ Saved';
            indicator.style.display = 'inline';
            indicator.style.color = '#2ecc71';
            
            setTimeout(() => {
                indicator.style.display = 'none';
            }, 2000);
        }
    }
    
    showUpdateInfo(username, timestamp) {
        const info = document.getElementById('notesInfo');
        if (info) {
            const timeAgo = this.getTimeAgo(timestamp);
            info.textContent = `Last updated by ${username} ${timeAgo}`;
            info.style.display = 'block';
        }
    }
    
    getTimeAgo(timestamp) {
        const now = Math.floor(Date.now() / 1000);
        const diff = now - timestamp;
        
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
        return Math.floor(diff / 86400) + ' days ago';
    }
    
    showNotification(message, type = 'info') {
        const modal = document.getElementById('notificationModal');
        const titleEl = document.getElementById('notificationTitle');
        const messageEl = document.getElementById('notificationMessage');
        const iconEl = document.getElementById('notificationIcon');
        const closeBtn = document.getElementById('closeNotificationBtn');
        
        if (!modal) return;
        
        // Set icon and color based on type
        if (type === 'error') {
            iconEl.textContent = 'error';
            iconEl.className = 'material-icons text-2xl text-red-500';
            titleEl.textContent = 'Error';
            titleEl.className = 'text-lg font-bold text-slate-900 dark:text-slate-100';
        } else if (type === 'success') {
            iconEl.textContent = 'check_circle';
            iconEl.className = 'material-icons text-2xl text-green-500';
            titleEl.textContent = 'Success';
            titleEl.className = 'text-lg font-bold text-slate-900 dark:text-slate-100';
        } else {
            iconEl.textContent = 'info';
            iconEl.className = 'material-icons text-2xl text-blue-500';
            titleEl.textContent = 'Notification';
            titleEl.className = 'text-lg font-bold text-slate-900 dark:text-slate-100';
        }
        
        messageEl.textContent = message;
        modal.style.display = 'flex';
        
        if (closeBtn) {
            closeBtn.onclick = () => {
                modal.style.display = 'none';
            };
        }
        
        // Close modal when clicking outside
        modal.onclick = (e) => {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        };
    }
}