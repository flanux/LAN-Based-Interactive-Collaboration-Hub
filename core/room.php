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
            <div class="event-log">
                <h2>Event Log (Debug)</h2>
                <div id="eventLog"></div>
            </div>
            
            <div class="test-controls">
                <h2>Test Event System</h2>
                <input type="text" id="testMessage" placeholder="Type a test message">
                <button onclick="sendTestEvent()">Send Test Event</button>
            </div>
        </main>
    </div>
    
    <script src="public/app.js"></script>
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