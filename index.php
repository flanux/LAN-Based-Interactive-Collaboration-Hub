<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "primary": "#059669",
                        "primary-hover": "#047857",
                        "background-offwhite": "#f8fafc",
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
    <style type="text/tailwindcss">
        @layer base {
            input, select {
                @apply bg-white border-slate-300 text-slate-900 focus:border-primary focus:ring-primary !important;
            }
        }
    </style>
    <title>LAN Collaboration Hub</title>
</head>
<body class="bg-background-offwhite font-display text-slate-900 min-h-screen flex items-center justify-center p-6 overflow-hidden">
    <div class="fixed inset-0 overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-primary/5 blur-[120px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] rounded-full bg-primary/10 blur-[120px]"></div>
    </div>
    
    <div class="w-full max-w-5xl">
        <main class="grid grid-cols-1 md:grid-cols-2 gap-8 items-stretch">
            <!-- Create Room Section -->
            <section class="bg-white border border-slate-200 rounded-xl p-8 shadow-xl shadow-slate-200/50 flex flex-col">
                <div class="mb-8">
                    <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="material-icons text-primary">add_to_photos</span>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 text-slate-900">Host a Session</h2>
                    <p class="text-slate-500 text-sm">Create a new workspace for your classroom to start collaborating instantly.</p>
                </div>
                <div class="space-y-6 flex-grow">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="create-name">Your Full Name</label>
                        <input class="w-full px-4 py-3 border rounded-lg outline-none transition-all" id="create-name" placeholder="e.g. Dr. Jane Smith" type="text" value="Teacher">
                    </div>
                </div>
                <div class="mt-8">
                    <button onclick="createRoom()" class="w-full bg-primary hover:bg-primary-hover text-white font-semibold py-3.5 px-6 rounded-lg shadow-lg shadow-emerald-900/10 transition-all flex items-center justify-center space-x-2 group">
                        <span>Create New Room</span>
                        <span class="material-icons text-sm group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                    <p class="text-center text-xs text-slate-400 mt-4">Generates a unique access code automatically</p>
                </div>
                <div id="create-status" class="mt-4 text-sm text-center"></div>
            </section>
            
            <!-- Join Room Section -->
            <section class="bg-white border border-slate-200 rounded-xl p-8 shadow-xl shadow-slate-200/50 flex flex-col">
                <div class="mb-8">
                    <div class="w-12 h-12 bg-emerald-50 rounded-lg flex items-center justify-center mb-4">
                        <span class="material-icons text-primary">group_add</span>
                    </div>
                    <h2 class="text-2xl font-bold mb-2 text-slate-900">Join a Session</h2>
                    <p class="text-slate-500 text-sm">Enter an existing room using a code shared by your teacher or peer.</p>
                </div>
                <div class="space-y-5 flex-grow">
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700" for="join-name">Your Name</label>
                        <input class="w-full px-4 py-3 border rounded-lg outline-none transition-all" id="join-name" placeholder="Enter your name" type="text" value="Student">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="room-code">Room Code</label>
                            <input class="w-full px-4 py-3 border rounded-lg outline-none transition-all uppercase font-mono tracking-widest text-center" id="room-code" placeholder="XYZ-123" type="text" maxlength="6">
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700" for="role">Your Role</label>
                            <div class="relative">
                                <select class="w-full px-4 py-3 border rounded-lg outline-none transition-all appearance-none cursor-pointer pr-10" id="role">
                                    <option value="student">Student</option>
                                    <option value="teacher">Teacher</option>
                                </select>
                                <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none text-sm">expand_more</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <button onclick="joinRoom()" class="w-full bg-primary hover:bg-primary-hover text-white font-semibold py-3.5 px-6 rounded-lg shadow-lg shadow-emerald-900/10 transition-all flex items-center justify-center space-x-2 group">
                        <span>Join Session</span>
                        <span class="material-icons text-sm group-hover:translate-x-1 transition-transform">login</span>
                    </button>
                    <p class="text-center text-xs text-slate-400 mt-4">Ask your host for the 6-character room code</p>
                </div>
                <div id="join-status" class="mt-4 text-sm text-center"></div>
            </section>
        </main>
    </div>
    
    <script>
        async function createRoom() {
            const username = document.getElementById('create-name').value.trim();
            const statusEl = document.getElementById('create-status');
            
            if (!username) {
                statusEl.textContent = '⚠️ Please enter your name';
                statusEl.className = 'mt-4 text-sm text-center text-red-600';
                return;
            }
            
            statusEl.textContent = '⏳ Creating room...';
            statusEl.className = 'mt-4 text-sm text-center text-blue-600';
            
            try {
                const formData = new FormData();
                formData.append('action', 'create_room');
                formData.append('role', 'teacher');
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('username', username);
                    localStorage.setItem('role', 'teacher');
                    
                    const joinFormData = new FormData();
                    joinFormData.append('action', 'join_room');
                    joinFormData.append('roomId', data.roomId);
                    joinFormData.append('username', username);
                    joinFormData.append('role', 'teacher');
                    
                    await fetch('api.php', {
                        method: 'POST',
                        body: joinFormData
                    });
                    
                    window.location.href = `room.php?id=${data.roomId}`;
                } else {
                    statusEl.textContent = '❌ Error: ' + data.error;
                    statusEl.className = 'mt-4 text-sm text-center text-red-600';
                }
            } catch (error) {
                statusEl.textContent = '❌ Error: ' + error.message;
                statusEl.className = 'mt-4 text-sm text-center text-red-600';
            }
        }
        
        async function joinRoom() {
            const username = document.getElementById('join-name').value.trim();
            const roomCode = document.getElementById('room-code').value.trim().toUpperCase();
            const role = document.getElementById('role').value;
            const statusEl = document.getElementById('join-status');
            
            if (!username) {
                statusEl.textContent = '⚠️ Please enter your name';
                statusEl.className = 'mt-4 text-sm text-center text-red-600';
                return;
            }
            
            if (!roomCode || roomCode.length !== 6) {
                statusEl.textContent = '⚠️ Please enter a valid 6-character room code';
                statusEl.className = 'mt-4 text-sm text-center text-red-600';
                return;
            }
            
            statusEl.textContent = '⏳ Joining room...';
            statusEl.className = 'mt-4 text-sm text-center text-blue-600';
            
            try {
                const formData = new FormData();
                formData.append('action', 'join_room');
                formData.append('roomId', roomCode);
                formData.append('username', username);
                formData.append('role', role);
                
                const response = await fetch('api.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    localStorage.setItem('username', username);
                    localStorage.setItem('role', role);
                    
                    window.location.href = `room.php?id=${roomCode}`;
                } else {
                    statusEl.textContent = '❌ Error: ' + data.error;
                    statusEl.className = 'mt-4 text-sm text-center text-red-600';
                }
            } catch (error) {
                statusEl.textContent = '❌ Error: ' + error.message;
                statusEl.className = 'mt-4 text-sm text-center text-red-600';
            }
        }
        
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                if (document.activeElement.id === 'room-code' || 
                    document.activeElement.id === 'join-name') {
                    joinRoom();
                } else if (document.activeElement.id === 'create-name') {
                    createRoom();
                }
            }
        });
    </script>
</body>
</html>