// features/notes/client.js - Client-side notes handling

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
        
        // Load existing notes
        this.loadNotes();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Listen for notes events
        this.app.on('notes_updated', (data) => {
            this.onNotesUpdated(data);
        });
        
        this.app.on('notes_cleared', (data) => {
            this.onNotesCleared(data);
        });
        
        // Set read-only for students
        if (this.app.role !== 'teacher') {
            this.textarea.readOnly = true;
            this.textarea.placeholder = 'Teacher notes will appear here...';
            
            // Hide clear button for students
            const clearBtn = document.getElementById('clearNotesBtn');
            if (clearBtn) {
                clearBtn.style.display = 'none';
            }
        }
    }
    
    setupEventListeners() {
        if (this.app.role === 'teacher') {
            // Auto-save as teacher types (with debounce)
            this.textarea.addEventListener('input', () => {
                this.autoSave();
            });
            
            // Save on blur
            this.textarea.addEventListener('blur', () => {
                this.saveNotes();
            });
            
            // Clear button
            const clearBtn = document.getElementById('clearNotesBtn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    this.clearNotes();
                });
            }
        }
    }
    
    async loadNotes() {
        try {
            const response = await fetch(`api.php?action=get_notes&roomId=${this.app.roomId}`);
            const data = await response.json();
            
            if (data.success) {
                this.lastContent = data.content;
                this.textarea.value = data.content;
                
                // Show last update info
                if (data.updatedBy && data.updatedAt) {
                    this.showUpdateInfo(data.updatedBy, data.updatedAt);
                }
            }
        } catch (error) {
            console.error('Error loading notes:', error);
        }
    }
    
    autoSave() {
        // Clear existing timeout
        if (this.saveTimeout) {
            clearTimeout(this.saveTimeout);
        }
        
        // Set new timeout (save 1 second after user stops typing)
        this.saveTimeout = setTimeout(() => {
            this.saveNotes();
        }, 1000);
    }
    
    async saveNotes() {
        const content = this.textarea.value;
        
        // Don't save if content hasn't changed
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
        // Don't update if this user is the one who updated it
        if (data.updatedBy === this.app.username) return;
        
        // Update textarea content
        this.textarea.value = data.content;
        this.lastContent = data.content;
        
        // Show update notification
        this.showUpdateInfo(data.updatedBy, data.updatedAt);
        
        // Log
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
}