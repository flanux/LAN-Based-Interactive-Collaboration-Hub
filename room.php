<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room - LAN Hub</title>
    <link rel="stylesheet" href="public/style.css">
</head>
<body>
    <div class="room-container">
        <header class="room-header">
            <div>
                <h1>Room: <span id="roomId">Loading...</span></h1>
                <p id="userInfo">Connecting...</p>
            </div>
            <div class="connection-status">
                <span id="connectionStatus" class="status-dot"></span>
                <span id="participantCount">0 participants</span>
            </div>
        </header>
        
        <main class="room-content">
            <div class="notes-panel">
                <h2>Shared Notes</h2>
                <div class="notes-controls">
                    <span id="saveIndicator" style="display: none;"></span>
                    <span id="notesInfo" class="notes-info"></span>
                    <button id="clearNotesBtn" class="btn-clear">Clear Notes</button>
                </div>
                <textarea id="notesTextarea" placeholder="Teacher can type notes here. Students will see them in real-time..."></textarea>
            </div>
            
            <div class="files-panel">
                <h2>Shared Files</h2>
                
                <div id="fileDropZone" class="drop-zone">
                    <div class="drop-zone-content">
                        <p>📁 Drag & drop files here</p>
                        <p>or</p>
                        <button id="browseBtn">Browse Files</button>
                        <input type="file" id="fileInput" multiple style="display: none;">
                    </div>
                </div>
                
                <div id="uploadStatus" class="upload-status" style="display: none;"></div>
                
                <div id="filesList" class="files-list">
                    <div class="no-files">No files shared yet</div>
                </div>
            </div>
            
            <div class="messages-panel">
                <h2>Messages</h2>
                <div id="messageDisplay"></div>
            </div>
            
            <div class="event-log">
                <h2>Event Log (Debug)</h2>
                <div id="eventLog"></div>
            </div>
            
            <div class="test-controls">
                <h2>Send Message</h2>
                <input type="text" id="testMessage" placeholder="Type a message to everyone in the room">
                <button onclick="sendTestEvent()">Send Message</button>
            </div>
        </main>
    </div>
    
    <script src="public/app.js"></script>
    <script src="features/files/client.js"></script>
    <script src="features/notes/client.js"></script>
    <script>
        // Get room ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const roomId = urlParams.get('id');
        
        if (!roomId) {
            alert('No room ID provided');
            window.location.href = 'index.php';
        }
        
        // Get user info from localStorage
        const username = localStorage.getItem('username') || 'Anonymous';
        const role = localStorage.getItem('role') || 'student';
        
        // Update UI
        document.getElementById('roomId').textContent = roomId;
        document.getElementById('userInfo').textContent = `${username} (${role})`;
        
        // Initialize app
        const app = new App(roomId, username, role);
        app.start();
        
        // Initialize files feature
        const filesClient = new FilesClient(app);
        
        // Initialize notes feature
        const notesClient = new NotesClient(app);
        
        // Add handler for displaying messages
        app.on('test_message', function(data) {
            const messageDisplay = document.getElementById('messageDisplay');
            if (messageDisplay) {
                const msgDiv = document.createElement('div');
                msgDiv.className = 'message';
                
                const header = document.createElement('div');
                header.className = 'message-header';
                header.textContent = data.username;
                
                const body = document.createElement('div');
                body.className = 'message-body';
                body.textContent = data.message;
                
                const time = document.createElement('div');
                time.className = 'message-time';
                time.textContent = new Date().toLocaleTimeString();
                
                msgDiv.appendChild(header);
                msgDiv.appendChild(body);
                msgDiv.appendChild(time);
                
                messageDisplay.appendChild(msgDiv);
                messageDisplay.scrollTop = messageDisplay.scrollHeight;
                
                // Limit messages
                while (messageDisplay.children.length > 50) {
                    messageDisplay.removeChild(messageDisplay.firstChild);
                }
            }
        });
        
        // Test function to send events
        function sendTestEvent() {
            const message = document.getElementById('testMessage').value;
            if (!message) return;
            
            app.emit('test_message', {
                username: username,
                message: message
            });
            
            document.getElementById('testMessage').value = '';
        }
        
        // Allow Enter key to send test message
        document.getElementById('testMessage').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendTestEvent();
            }
        });
    </script>
</body>
</html>