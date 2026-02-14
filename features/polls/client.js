class PollsClient {
    constructor(app) {
        this.app = app;
        this.polls = [];
        this.pendingDeletePollId = null;
        this.init();
    }
    
    init() {
        this.loadPolls();
        
        this.setupEventListeners();
        this.setupDeleteModal();
        
        this.app.on('poll_created', (data) => this.onPollCreated(data));
        this.app.on('vote_submitted', (data) => this.onVoteSubmitted(data));
        this.app.on('poll_closed', (data) => this.onPollClosed(data));
        this.app.on('poll_deleted', (data) => this.onPollDeleted(data));
        
        if (this.app.role !== 'teacher') {
            const createBtnContainer = document.getElementById('createPollBtnContainer');
            if (createBtnContainer) {
                createBtnContainer.style.display = 'none';
            }
        }
    }
    
    setupDeleteModal() {
        const modal = document.getElementById('deletePollModal');
        const cancelBtn = document.getElementById('cancelDeleteBtn');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.pendingDeletePollId = null;
                modal.style.display = 'none';
            });
        }
        
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                if (this.pendingDeletePollId) {
                    this.confirmDeletePoll(this.pendingDeletePollId);
                    modal.style.display = 'none';
                    this.pendingDeletePollId = null;
                }
            });
        }
        
        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.pendingDeletePollId = null;
                modal.style.display = 'none';
            }
        });
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
    
    setupEventListeners() {
        const createBtn = document.getElementById('createPollBtn');
        if (createBtn && this.app.role === 'teacher') {
            createBtn.addEventListener('click', () => this.showCreateForm());
        }
        
        const submitPollBtn = document.getElementById('submitPollBtn');
        if (submitPollBtn) {
            submitPollBtn.addEventListener('click', () => this.createPoll());
        }
        
        const cancelPollBtn = document.getElementById('cancelPollBtn');
        if (cancelPollBtn) {
            cancelPollBtn.addEventListener('click', () => this.hideCreateForm());
        }
        
        const addOptionBtn = document.getElementById('addOptionBtn');
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', () => this.addOption());
        }
    }
    
    async loadPolls() {
        try {
            const response = await fetch(`api.php?action=get_polls&roomId=${this.app.roomId}`);
            const data = await response.json();
            
            if (data.success) {
                this.polls = data.polls;
                this.renderPolls();
            }
        } catch (error) {
            console.error('Error loading polls:', error);
        }
    }
    
    showCreateForm() {
        const form = document.getElementById('pollForm');
        const createBtn = document.getElementById('createPollBtn');
        if (form && createBtn) {
            form.style.display = 'block';
            createBtn.style.display = 'none';
            
            document.getElementById('pollQuestion').value = '';
            const optionsDiv = document.getElementById('pollOptions');
            optionsDiv.innerHTML = '';
            
            // Add first two default options
            for (let i = 1; i <= 2; i++) {
                const optionContainer = document.createElement('div');
                optionContainer.className = 'flex gap-2 items-center';
                
                const input = document.createElement('input');
                input.type = 'text';
                input.className = 'flex-1 px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-md outline-none focus:border-primary text-slate-900 dark:text-slate-100 bg-white dark:bg-zinc-800';
                input.placeholder = `Option ${i}`;
                
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md transition-colors';
                deleteBtn.innerHTML = '<span class="material-icons text-sm">close</span>';
                deleteBtn.onclick = (e) => {
                    e.preventDefault();
                    optionContainer.remove();
                };
                
                optionContainer.appendChild(input);
                optionContainer.appendChild(deleteBtn);
                optionsDiv.appendChild(optionContainer);
            }
        }
    }
    
    hideCreateForm() {
        const form = document.getElementById('pollForm');
        const createBtn = document.getElementById('createPollBtn');
        if (form && createBtn) {
            form.style.display = 'none';
            createBtn.style.display = 'block';
        }
    }
    
    addOption() {
        const optionsDiv = document.getElementById('pollOptions');
        const currentOptions = optionsDiv.querySelectorAll('input[type="text"]');
        const newIndex = currentOptions.length + 1;
        
        const optionContainer = document.createElement('div');
        optionContainer.className = 'flex gap-2 items-center';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.className = 'flex-1 px-3 py-2 border border-slate-300 dark:border-zinc-700 rounded-md outline-none focus:border-primary text-slate-900 dark:text-slate-100 bg-white dark:bg-zinc-800';
        input.placeholder = `Option ${newIndex}`;
        
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md transition-colors';
        deleteBtn.innerHTML = '<span class="material-icons text-sm">close</span>';
        deleteBtn.onclick = (e) => {
            e.preventDefault();
            optionContainer.remove();
        };
        
        optionContainer.appendChild(input);
        optionContainer.appendChild(deleteBtn);
        optionsDiv.appendChild(optionContainer);
    }
    
    async createPoll() {
        const question = document.getElementById('pollQuestion').value.trim();
        const optionInputs = document.getElementById('pollOptions').querySelectorAll('input[type="text"]');
        
        const options = [];
        optionInputs.forEach(input => {
            const value = input.value.trim();
            if (value) options.push(value);
        });
        
        if (!question) {
            alert('Please enter a question');
            return;
        }
        
        if (options.length < 2) {
            alert('Please add at least 2 options');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'create_poll');
            formData.append('roomId', this.app.roomId);
            formData.append('question', question);
            formData.append('options', JSON.stringify(options));
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.hideCreateForm();
            } else {
                alert('Error creating poll: ' + data.error);
            }
        } catch (error) {
            console.error('Error creating poll:', error);
            alert('Error creating poll');
        }
    }
    
    async submitVote(pollId, optionIndex) {
        try {
            const formData = new FormData();
            formData.append('action', 'submit_vote');
            formData.append('roomId', this.app.roomId);
            formData.append('pollId', pollId);
            formData.append('optionIndex', optionIndex);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!data.success) {
                alert('Error submitting vote: ' + data.error);
            }
        } catch (error) {
            console.error('Error submitting vote:', error);
        }
    }
    
    async closePoll(pollId) {
        try {
            const formData = new FormData();
            formData.append('action', 'close_poll');
            formData.append('roomId', this.app.roomId);
            formData.append('pollId', pollId);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!data.success) {
                alert('Error closing poll: ' + data.error);
            }
        } catch (error) {
            console.error('Error closing poll:', error);
        }
    }
    
    deletePoll(pollId) {
        this.pendingDeletePollId = pollId;
        const modal = document.getElementById('deletePollModal');
        if (modal) {
            modal.style.display = 'flex';
        }
    }
    
    async confirmDeletePoll(pollId) {
        try {
            const formData = new FormData();
            formData.append('action', 'delete_poll');
            formData.append('roomId', this.app.roomId);
            formData.append('pollId', pollId);
            formData.append('username', this.app.username);
            
            const response = await fetch('api.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (!data.success) {
                this.showNotification('Error deleting poll: ' + data.error, 'error');
            } else {
                this.showNotification('Poll deleted successfully', 'success');
            }
        } catch (error) {
            console.error('Error deleting poll:', error);
            this.showNotification('Error deleting poll. Please try again.', 'error');
        }
    }
    
    onPollCreated(poll) {
        const exists = this.polls.find(p => p.id === poll.id);
        if (exists) return;
        
        this.polls.push(poll);
        this.renderPolls();
        this.app.log(`Poll created: ${poll.question}`);
    }
    
    onVoteSubmitted(data) {
        const poll = this.polls.find(p => p.id === data.pollId);
        if (poll) {
            if (!poll.votes) poll.votes = {};
            poll.votes[data.username] = data.optionIndex;
            this.renderPolls();
        }
    }
    
    onPollClosed(data) {
        const poll = this.polls.find(p => p.id === data.pollId);
        if (poll) {
            poll.active = false;
            this.renderPolls();
            this.app.log(`Poll closed: ${poll.question}`);
        }
    }
    
    onPollDeleted(data) {
        this.polls = this.polls.filter(p => p.id !== data.pollId);
        this.renderPolls();
        this.app.log('Poll deleted');
    }
    
    renderPolls() {
        const pollsList = document.getElementById('pollsList');
        if (!pollsList) return;
        
        if (this.polls.length === 0) {
            pollsList.innerHTML = '<div class="text-center text-slate-400 py-4 text-sm">No polls yet</div>';
            return;
        }
        
        pollsList.innerHTML = '';
        
        this.polls.forEach(poll => {
            const pollDiv = document.createElement('div');
            pollDiv.className = `p-4 bg-slate-50 dark:bg-zinc-800/50 rounded-lg border border-slate-100 dark:border-zinc-800 ${!poll.active ? 'opacity-75' : ''}`;
            
            const question = document.createElement('div');
            question.className = 'font-semibold text-slate-900 dark:text-slate-100 mb-2';
            question.textContent = poll.question;
            
            const status = document.createElement('div');
            status.className = 'text-xs font-medium text-slate-500 mb-3';
            status.textContent = poll.active ? '🟢 Active' : '🔴 Closed';
            
            pollDiv.appendChild(question);
            pollDiv.appendChild(status);
            
            const totalVotes = Object.keys(poll.votes || {}).length;
            const voteCounts = {};
            poll.options.forEach((opt, idx) => voteCounts[idx] = 0);
            
            Object.values(poll.votes || {}).forEach(optIdx => {
                voteCounts[optIdx] = (voteCounts[optIdx] || 0) + 1;
            });
            
            const optionsDiv = document.createElement('div');
            optionsDiv.className = 'space-y-2 mb-3';
            
            poll.options.forEach((option, index) => {
                const optionDiv = document.createElement('div');
                optionDiv.className = 'w-full';
                
                const userVoted = poll.votes && poll.votes[this.app.username] === index;
                const voteCount = voteCounts[index] || 0;
                const percentage = totalVotes > 0 ? Math.round((voteCount / totalVotes) * 100) : 0;
                
                const showResults = !poll.active || this.app.role === 'teacher' || poll.votes[this.app.username] !== undefined;
                
                if (poll.active && !showResults) {
                    const btn = document.createElement('button');
                    btn.className = 'w-full bg-primary hover:bg-primary/90 text-zinc-900 font-medium py-2 px-3 rounded-md transition-colors text-sm';
                    btn.textContent = option;
                    btn.onclick = () => this.submitVote(poll.id, index);
                    optionDiv.appendChild(btn);
                } else {
                    const resultDiv = document.createElement('div');
                    resultDiv.className = `relative bg-white dark:bg-zinc-700 border ${userVoted ? 'border-primary/30' : 'border-slate-100 dark:border-zinc-600'} rounded-md p-3 overflow-hidden`;
                    
                    const bar = document.createElement('div');
                    bar.className = 'absolute left-0 top-0 height-full bg-primary/10 transition-all duration-500';
                    bar.style.width = percentage + '%';
                    bar.style.height = '100%';
                    
                    const text = document.createElement('div');
                    text.className = 'font-medium text-slate-900 dark:text-slate-200 text-sm relative z-10 mb-1';
                    text.textContent = option + (userVoted ? ' ✓' : '');
                    
                    const stats = document.createElement('div');
                    stats.className = 'text-xs text-slate-500 dark:text-slate-400 relative z-10';
                    stats.textContent = `${voteCount} vote${voteCount !== 1 ? 's' : ''} (${percentage}%)`;
                    
                    resultDiv.appendChild(bar);
                    resultDiv.appendChild(text);
                    resultDiv.appendChild(stats);
                    
                    optionDiv.appendChild(resultDiv);
                }
                
                optionsDiv.appendChild(optionDiv);
            });
            
            pollDiv.appendChild(optionsDiv);
            
            if (this.app.role === 'teacher') {
                const controls = document.createElement('div');
                controls.className = 'flex gap-2 items-center pt-3 border-t border-slate-100 dark:border-zinc-700';
                
                if (poll.active) {
                    const closeBtn = document.createElement('button');
                    closeBtn.className = 'bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-1 px-3 rounded text-xs transition-colors';
                    closeBtn.textContent = 'Close';
                    closeBtn.onclick = () => this.closePoll(poll.id);
                    controls.appendChild(closeBtn);
                }
                
                const deleteBtn = document.createElement('button');
                deleteBtn.className = 'bg-red-500 hover:bg-red-600 text-white font-medium py-1 px-3 rounded text-xs transition-colors';
                deleteBtn.textContent = 'Delete';
                deleteBtn.onclick = () => this.deletePoll(poll.id);
                controls.appendChild(deleteBtn);
                
                const totalDiv = document.createElement('span');
                totalDiv.className = 'ml-auto text-xs text-slate-500 dark:text-slate-400 font-medium';
                totalDiv.textContent = `${totalVotes} vote${totalVotes !== 1 ? 's' : ''}`;
                controls.appendChild(totalDiv);
                
                pollDiv.appendChild(controls);
            }
            
            pollsList.appendChild(pollDiv);
        });
    }
}