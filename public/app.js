i// public/app.js - Core client application with event bus and polling

class App {
    constructor(roomId, username, role) {
        this.roomId = roomId;
        this.username = username;
        this.role = role;
        this.lastEventId = 0;
        this.isPolling = false;
        this.pollInterval = 2000; // Poll every 2 seconds
        this.eventHandlers = {};
        this.connected = false;
    }
    
    // Start the application
    async start() {
        console.log('Starting app for room:', this.roomId);
        
        // Set up event handlers
        this.setupEventHandlers();
        
        // Load initial room state
        await this.loadRoomState();
        
        // Start polling
        this.startPolling();
        
        console.log('App started successfully');
    }
    
    // Set up event handlers
    setupEventHandlers() {
        // Handle room created event
        this.on('room_created', (data) => {
            console.log('Room created:', data);
            this.log('Room created by ' + data.role);
        });
        
        // Handle user joined event
        this.on('user_joined', (data) => {
            console.log('User joined:', data);
            this.log(data.username + ' (' + data.role + ') joined the room');
            this.updateParticipantCount();
        });
        
        // Handle test message event
        this.on('test_message', (data) => {
            console.log('Test message received:', data);
            this.log(data.username + ': ' + data.message);
        });
    }
    
    // Load initial room state
    async loadRoomState() {
        try {
            const response = await fetch(`api.php?action=get_room&roomId=${this.roomId}`);
            const data = await response.json();
            
            if (data.success) {
                console.log('Room state loaded:', data.room);
                this.updateParticipantCount(data.room.participants.length);
            } else {
                console.error('Failed to load room state:', data.error);
                alert('Error: ' + data.error);
                window.location.href = 'index.php';
            }
        } catch (error) {
            console.error('Error loading room state:', error);
        }
    }
    
    // Start polling for events
    startPolling() {
        if (this.isPolling) return;
        
        this.isPolling = true;
        this.poll();
    }
    
    // Stop polling
    stopPolling() {
        this.isPolling = false;
    }
    
    // Poll for new events
    async poll() {
        if (!this.isPolling) return;
        
        try {
            const response = await fetch(
                `api.php?action=poll&roomId=${this.roomId}&after=${this.lastEventId}`
            );
            const data = await response.json();
            
            if (data.success) {
                // Update connection status
                if (!this.connected) {
                    this.connected = true;
                    this.updateConnectionStatus(true);
                }
                
                // Process new events
                if (data.events && data.events.length > 0) {
                    console.log('Received events:', data.events);
                    
                    data.events.forEach(event => {
                        // Update last event ID
                        if (event.id > this.lastEventId) {
                            this.lastEventId = event.id;
                        }
                        
                        // Emit event to handlers
                        this.emitLocal(event.type, event.data);
                    });
                }
            } else {
                console.error('Poll error:', data.error);
                this.connected = false;
                this.updateConnectionStatus(false);
            }
        } catch (error) {
            console.error('Poll request failed:', error);
            this.connected = false;
            this.updateConnectionStatus(false);
        }
        
        // Schedule next poll
        setTimeout(() => this.poll(), this.pollInterval);
    }
    
    // Emit event to server
    async emit(eventType, eventData) {
        try {
            const formData = new FormData();
            formData.append('action', 'emit');
            formData.append('roomId', this.roomId);
            formData.append('type', eventType);
            formData.append('data', JSON.stringify(eventData));
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                console.log('Event emitted:', eventType, eventData);
            } else {
                console.error('Failed to emit event:', data.error);
            }
        } catch (error) {
            console.error('Error emitting event:', error);
        }
    }
    
    // Emit event locally (to handlers)
    emitLocal(eventType, eventData) {
        if (this.eventHandlers[eventType]) {
            this.eventHandlers[eventType].forEach(handler => {
                handler(eventData);
            });
        }
    }
    
    // Register event handler
    on(eventType, handler) {
        if (!this.eventHandlers[eventType]) {
            this.eventHandlers[eventType] = [];
        }
        this.eventHandlers[eventType].push(handler);
    }
    
    // Update connection status UI
    updateConnectionStatus(connected) {
        const statusDot = document.getElementById('connectionStatus');
        if (statusDot) {
            statusDot.className = 'status-dot ' + (connected ? 'connected' : 'disconnected');
            statusDot.title = connected ? 'Connected' : 'Disconnected';
        }
    }
    
    // Update participant count UI
    async updateParticipantCount(count) {
        if (count === undefined) {
            // Fetch from server
            try {
                const response = await fetch(`api.php?action=get_room&roomId=${this.roomId}`);
                const data = await response.json();
                
                if (data.success) {
                    count = data.room.participants.length;
                }
            } catch (error) {
                console.error('Error fetching participant count:', error);
                return;
            }
        }
        
        const countElement = document.getElementById('participantCount');
        if (countElement) {
            countElement.textContent = count + ' participant' + (count !== 1 ? 's' : '');
        }
    }
    
    // Log message to event log (debug)
    log(message) {
        const eventLog = document.getElementById('eventLog');
        if (eventLog) {
            const entry = document.createElement('div');
            entry.className = 'log-entry';
            
            const timestamp = new Date().toLocaleTimeString();
            entry.textContent = `[${timestamp}] ${message}`;
            
            eventLog.appendChild(entry);
            
            // Auto-scroll to bottom
            eventLog.scrollTop = eventLog.scrollHeight;
            
            // Limit log entries
            while (eventLog.children.length > 50) {
                eventLog.removeChild(eventLog.firstChild);
            }
        }
    }
}