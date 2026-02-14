<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LAN Collaboration | Room</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#059669",
                        "background-light": "#f6f8f6",
                        "background-dark": "#102216",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #1e3a24;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 min-h-screen">
    <header class="sticky top-0 z-50 bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 shadow-sm">
        <div class="max-w-[1600px] mx-auto px-6 h-16 flex items-center justify-center">
            <div class="flex items-center space-x-12">
                <div class="flex flex-col items-center">
                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Room Code</span>
                    <div class="flex items-center space-x-1">
                        <span class="font-mono font-semibold text-primary" id="roomIdDisplay">#ABCD</span>
                        <button id="copyRoomCodeBtn" class="p-1 text-slate-500 hover:text-primary hover:bg-slate-100 dark:hover:bg-zinc-800 rounded transition" title="Copy room code">
                            <span class="material-icons text-sm">content_copy</span>
                        </button>
                    </div>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-zinc-800"></div>
                <div class="flex flex-col items-center">
                    <span class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">User</span>
                    <div class="flex items-center space-x-1">
                        <span class="font-medium" id="userDisplay">Loading...</span>
                        <span class="text-xs text-slate-500 dark:text-slate-400" id="userRoleDisplay"></span>
                    </div>
                </div>
                <div class="h-8 w-px bg-slate-200 dark:bg-zinc-800"></div>
                <div class="flex items-center bg-primary/10 px-4 py-1.5 rounded-full border border-primary/20">
                    <span class="relative flex h-2 w-2 mr-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                    </span>
                    <span class="text-sm font-semibold text-slate-700 dark:text-slate-200" id="participantCountDisplay">0 Participants</span>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-[1600px] mx-auto p-6">
        <div class="grid grid-cols-12 gap-6">
            <!-- Main Content Area -->
            <div class="col-span-12 lg:col-span-8 space-y-6">
                <!-- Polls & Quizzes and File Sharing -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Polls Panel -->
                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-zinc-800">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex items-center space-x-3">
                                <div class="p-2 bg-primary/20 rounded-lg text-primary">
                                    <span class="material-icons">poll</span>
                                </div>
                                <h2 class="font-bold text-lg">Polls & Quizzes</h2>
                            </div>
                            <span class="text-xs font-medium text-slate-400" id="pollCount">0 Active</span>
                        </div>
                        <div id="pollsList" class="space-y-3 mb-6">
                            <div class="text-center text-slate-400 py-4 text-sm">No polls yet</div>
                        </div>
                        <div id="createPollBtnContainer">
                            <button id="createPollBtn" class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-3 rounded-lg transition-all flex items-center justify-center space-x-2">
                                <span class="material-icons text-xl">add_circle</span>
                                <span>Create New Poll</span>
                            </button>
                        </div>
                        
                        <!-- Poll Form (hidden by default) -->
                        <div id="pollForm" style="display: none;" class="mt-4 space-y-4">
                            <input type="text" id="pollQuestion" placeholder="Enter your question" class="w-full px-4 py-2 border border-slate-300 dark:border-zinc-700 rounded-lg outline-none focus:border-primary">
                            <div id="pollOptions" class="space-y-2"></div>
                            <div class="flex gap-2">
                                <button id="addOptionBtn" class="flex-1 bg-slate-200 dark:bg-zinc-800 text-slate-900 dark:text-slate-100 py-2 rounded-lg hover:bg-slate-300 transition">+ Option</button>
                                <button id="submitPollBtn" class="flex-1 bg-primary text-zinc-900 py-2 rounded-lg font-semibold hover:bg-primary/90 transition">Create</button>
                                <button id="cancelPollBtn" class="flex-1 bg-slate-200 dark:bg-zinc-800 text-slate-900 dark:text-slate-100 py-2 rounded-lg hover:bg-slate-300 transition">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <!-- File Sharing Panel -->
                    <div class="bg-white dark:bg-zinc-900 p-6 rounded-xl shadow-sm border border-slate-100 dark:border-zinc-800">
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="p-2 bg-primary/20 rounded-lg text-primary">
                                <span class="material-icons">cloud_upload</span>
                            </div>
                            <h2 class="font-bold text-lg">File Sharing</h2>
                        </div>
                        <div id="fileDropZone" class="border-2 border-dashed border-slate-200 dark:border-zinc-700 rounded-xl p-8 flex flex-col items-center justify-center text-center space-y-3 mb-4 group hover:border-primary/50 transition-colors">
                            <span class="material-icons text-4xl text-slate-300 dark:text-zinc-600 group-hover:text-primary transition-colors">folder_zip</span>
                            <p class="text-sm text-slate-500">Drag & Drop files here or</p>
                            <button id="browseBtn" class="text-primary font-semibold text-sm hover:underline">Browse Files</button>
                            <input type="file" id="fileInput" multiple style="display: none;">
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                            <span>Max file size: 50MB</span>
                            <span>Shared with all</span>
                        </div>
                        <div id="uploadStatus" style="display: none;" class="mt-3 text-sm text-green-600"></div>
                        <div id="filesList" class="mt-4 space-y-2">
                            <div class="text-center text-slate-400 py-4 text-sm">No files shared yet</div>
                        </div>
                    </div>
                </div>

                <!-- Collaborative Notes -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-100 dark:border-zinc-800 overflow-hidden">
                    <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between bg-slate-50/50 dark:bg-zinc-800/20">
                        <div class="flex items-center space-x-3">
                            <div class="p-1.5 bg-primary/20 rounded text-primary">
                                <span class="material-icons text-sm">edit_note</span>
                            </div>
                            <h2 class="font-bold">Collaborative Notes</h2>
                        </div>
                        <div class="flex space-x-2">
                            <button id="saveNotesBtn" class="text-xs px-3 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-md hover:bg-white dark:hover:bg-zinc-800 transition-colors" style="display: none;">Save PDF</button>
                            <span id="saveIndicator" style="display: none;" class="text-xs px-3 py-1.5 text-slate-500"></span>
                            <button id="clearNotesBtn" class="text-xs px-3 py-1.5 border border-red-200 text-red-500 rounded-md hover:bg-red-50 transition-colors">Clear Notes</button>
                        </div>
                    </div>
                    <div class="p-6">
                        <textarea id="notesTextarea" class="w-full h-64 bg-transparent border-none focus:ring-0 text-slate-700 dark:text-slate-300 resize-none font-display text-base leading-relaxed p-0" placeholder="Type session notes here for all participants to see..."></textarea>
                    </div>
                    <div class="px-6 py-3 bg-slate-50 dark:bg-zinc-800/40 border-t border-slate-100 dark:border-zinc-800">
                        <span id="notesInfo" class="text-[10px] text-slate-400 italic"></span>
                    </div>
                </div>
            </div>

            <!-- Chat Sidebar -->
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-slate-100 dark:border-zinc-800 flex flex-col h-[calc(100vh-112px)] sticky top-20">
                    <div class="p-4 border-b border-slate-100 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="material-icons text-primary">chat_bubble</span>
                            <h2 class="font-bold">Class Chat</h2>
                        </div>
                        <span class="bg-slate-100 dark:bg-zinc-800 text-[10px] font-bold px-2 py-0.5 rounded text-slate-500 uppercase">Live</span>
                    </div>
                    <div id="messageDisplay" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar">
                        <div class="text-center">
                            <span class="text-[10px] bg-slate-50 dark:bg-zinc-800/50 px-2 py-1 rounded text-slate-400">Session started</span>
                        </div>
                    </div>
                    <div class="p-4 border-t border-slate-100 dark:border-zinc-800">
                        <div class="relative flex items-center">
                            <input id="messageInput" class="w-full bg-slate-50 dark:bg-zinc-800 border-slate-200 dark:border-zinc-700 border rounded-full py-3 pl-4 pr-12 text-sm focus:ring-primary focus:border-primary outline-none" placeholder="Message class..." type="text"/>
                            <button id="sendMessageBtn" class="absolute right-1.5 p-2 bg-primary text-white rounded-full hover:scale-105 transition-transform flex items-center justify-center">
                                <span class="material-icons">send</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Delete Poll Modal -->
    <div id="deletePollModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-slate-200 dark:border-zinc-800 max-w-sm w-full mx-4">
            <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                    <span class="material-icons text-red-500">warning</span>
                    <span>Delete Poll</span>
                </h2>
            </div>
            <div class="p-6">
                <p class="text-slate-700 dark:text-slate-300 mb-2">Are you sure you want to delete this poll?</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">This action cannot be undone. All votes will be lost.</p>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button id="cancelDeleteBtn" class="flex-1 bg-slate-200 dark:bg-zinc-800 text-slate-900 dark:text-slate-100 font-semibold py-2.5 rounded-lg hover:bg-slate-300 dark:hover:bg-zinc-700 transition">
                    Cancel
                </button>
                <button id="confirmDeleteBtn" class="flex-1 bg-red-500 text-white font-semibold py-2.5 rounded-lg hover:bg-red-600 transition">
                    Delete
                </button>
            </div>
        </div>
    </div>
    
    <!-- Error/Notification Modal -->
    <div id="notificationModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-slate-200 dark:border-zinc-800 max-w-sm w-full mx-4">
            <div class="p-6 border-b border-slate-200 dark:border-zinc-800 flex items-center space-x-2">
                <span id="notificationIcon" class="material-icons text-2xl">info</span>
                <h2 id="notificationTitle" class="text-lg font-bold text-slate-900 dark:text-slate-100">Notification</h2>
            </div>
            <div class="p-6">
                <p id="notificationMessage" class="text-slate-700 dark:text-slate-300"></p>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button id="closeNotificationBtn" class="flex-1 bg-slate-200 dark:bg-zinc-800 text-slate-900 dark:text-slate-100 font-semibold py-2.5 rounded-lg hover:bg-slate-300 dark:hover:bg-zinc-700 transition">
                    OK
                </button>
            </div>
        </div>
    </div>
    
    <!-- Clear Notes Modal -->
    <div id="clearNotesModal" style="display: none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl border border-slate-200 dark:border-zinc-800 max-w-sm w-full mx-4">
            <div class="p-6 border-b border-slate-200 dark:border-zinc-800">
                <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100 flex items-center space-x-2">
                    <span class="material-icons text-orange-500">warning</span>
                    <span>Clear Notes</span>
                </h2>
            </div>
            <div class="p-6">
                <p class="text-slate-700 dark:text-slate-300 mb-2">Are you sure you want to clear all notes?</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">This action cannot be undone. All notes will be permanently deleted.</p>
            </div>
            <div class="p-6 border-t border-slate-200 dark:border-zinc-800 flex gap-3">
                <button id="cancelClearBtn" class="flex-1 bg-slate-200 dark:bg-zinc-800 text-slate-900 dark:text-slate-100 font-semibold py-2.5 rounded-lg hover:bg-slate-300 dark:hover:bg-zinc-700 transition">
                    Cancel
                </button>
                <button id="confirmClearBtn" class="flex-1 bg-orange-500 text-white font-semibold py-2.5 rounded-lg hover:bg-orange-600 transition">
                    Clear
                </button>
            </div>
        </div>
    </div>
    
    <script src="public/app.js"></script>
    <script src="features/files/client.js"></script>
    <script src="features/notes/client.js"></script>
    <script src="features/polls/client.js"></script>
    <script>
        const urlParams = new URLSearchParams(window.location.search);
        const roomId = urlParams.get('id');
        
        if (!roomId) {
            alert('No room ID provided');
            window.location.href = 'index.php';
        }
        
        let username = localStorage.getItem('username') || 'Anonymous';
        let role = localStorage.getItem('role') || 'student';
        
        // Function to fetch room info and determine actual role
        async function determineUserRole() {
            try {
                const response = await fetch(`api.php?action=get_room&roomId=${roomId}`);
                const data = await response.json();
                
                if (data.success && data.room) {
                    // Check if current user is the room host/creator
                    const roomHost = data.room.createdBy || null;
                    
                    // If this user is the host, they should be a teacher
                    if (roomHost && roomHost.toLowerCase() === username.toLowerCase()) {
                        role = 'teacher';
                        localStorage.setItem('role', 'teacher');
                    } else {
                        // Use whatever role is in localStorage (join role)
                        role = localStorage.getItem('role') || 'student';
                    }
                }
            } catch (error) {
                console.error('Error fetching room info:', error);
                // Fallback to localStorage role
                role = localStorage.getItem('role') || 'student';
            }
            
            // Update UI with final role
            initializeRoom();
        }
        
        function initializeRoom() {
            document.getElementById('roomIdDisplay').textContent = roomId.toUpperCase();
            document.getElementById('userDisplay').textContent = username;
            document.getElementById('userRoleDisplay').textContent = '(' + role + ')';
            
            // Setup copy room code button
            const copyBtn = document.getElementById('copyRoomCodeBtn');
            if (copyBtn) {
                copyBtn.addEventListener('click', () => {
                    const roomCode = roomId.toUpperCase();
                    navigator.clipboard.writeText(roomCode).then(() => {
                        // Visual feedback
                        const originalIcon = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<span class="material-icons text-sm">check</span>';
                        copyBtn.classList.add('text-green-500');
                        setTimeout(() => {
                            copyBtn.innerHTML = originalIcon;
                            copyBtn.classList.remove('text-green-500');
                        }, 1500);
                    }).catch(() => {
                        // Fallback for older browsers
                        const input = document.createElement('textarea');
                        input.value = roomId.toUpperCase();
                        document.body.appendChild(input);
                        input.select();
                        document.execCommand('copy');
                        document.body.removeChild(input);
                    });
                });
            }
            
            const app = new App(roomId, username, role);
            app.start();
            
            const filesClient = new FilesClient(app);
            
            const notesClient = new NotesClient(app);
            
            const pollsClient = new PollsClient(app);
            
            app.on('test_message', function(data) {
                const messageDisplay = document.getElementById('messageDisplay');
                if (messageDisplay) {
                    const msgDiv = document.createElement('div');
                    
                    // Determine if message is from current user
                    const isCurrentUser = data.username === username;
                    
                    if (isCurrentUser) {
                        msgDiv.className = 'flex flex-col items-end space-y-1';
                        msgDiv.innerHTML = `
                            <div class="flex items-center space-x-2">
                                <span class="text-[10px] text-slate-400">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                                <span class="text-xs font-bold text-primary">You</span>
                            </div>
                            <div class="bg-primary/20 border border-primary/20 px-4 py-2 rounded-2xl rounded-tr-none max-w-[85%]">
                                <p class="text-sm">${data.message}</p>
                            </div>
                        `;
                    } else {
                        msgDiv.className = 'flex flex-col items-start space-y-1';
                        msgDiv.innerHTML = `
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-bold text-slate-500">${data.username}</span>
                                <span class="text-[10px] text-slate-400">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                            </div>
                            <div class="bg-slate-100 dark:bg-zinc-800 px-4 py-2 rounded-2xl rounded-tl-none max-w-[85%]">
                                <p class="text-sm">${data.message}</p>
                            </div>
                        `;
                    }
                    
                    messageDisplay.appendChild(msgDiv);
                    messageDisplay.scrollTop = messageDisplay.scrollHeight;
                    
                    // Keep only last 50 messages
                    const messages = messageDisplay.querySelectorAll('[class*="flex-col"]');
                    while (messages.length > 50) {
                        messages[0].remove();
                    }
                }
            });
            
            const messageInput = document.getElementById('messageInput');
            const sendMessageBtn = document.getElementById('sendMessageBtn');
            
            function sendMessage() {
                const message = messageInput.value.trim();
                if (!message) return;
                
                app.emit('test_message', {
                    username: username,
                    message: message
                });
                
                messageInput.value = '';
            }
            
            if (sendMessageBtn) {
                sendMessageBtn.addEventListener('click', sendMessage);
            }
            
            if (messageInput) {
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendMessage();
                    }
                });
            }
        }
        
        // Start the initialization
        determineUserRole();
    </script>
</body>
</html>