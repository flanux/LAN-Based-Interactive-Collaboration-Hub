<?php
// features/polls/server.php - Handles polls/quizzes

require_once __DIR__ . '/../../core/storage.php';
require_once __DIR__ . '/../../core/eventbus.php';

class PollsFeature {
    private $storage;
    private $eventBus;
    
    public function __construct() {
        $this->storage = new Storage();
        $this->eventBus = new EventBus();
    }
    
    // Create a new poll
    public function createPoll($roomId, $question, $options, $username) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Validate inputs
        if (empty($question) || empty($options) || count($options) < 2) {
            return array(
                'success' => false,
                'error' => 'Poll needs a question and at least 2 options'
            );
        }
        
        // Get current state
        $state = $this->storage->getRoomState($roomId);
        
        // Initialize polls array if needed
        if (!isset($state['polls'])) {
            $state['polls'] = array();
        }
        
        // Create poll object
        $poll = array(
            'id' => uniqid(),
            'question' => $question,
            'options' => $options,
            'votes' => array(), // array of {username: optionIndex}
            'createdBy' => $username,
            'createdAt' => time(),
            'active' => true
        );
        
        // Add to polls array
        $state['polls'][] = $poll;
        
        // Update room state
        $this->storage->updateRoomState($roomId, array(
            'polls' => $state['polls']
        ));
        
        // Broadcast poll_created event
        $this->eventBus->emit($roomId, 'poll_created', $poll);
        
        return array(
            'success' => true,
            'poll' => $poll
        );
    }
    
    // Submit a vote
    public function submitVote($roomId, $pollId, $optionIndex, $username) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Get current state
        $state = $this->storage->getRoomState($roomId);
        $polls = isset($state['polls']) ? $state['polls'] : array();
        
        // Find the poll
        $pollIndex = -1;
        $poll = null;
        for ($i = 0; $i < count($polls); $i++) {
            if ($polls[$i]['id'] === $pollId) {
                $pollIndex = $i;
                $poll = $polls[$i];
                break;
            }
        }
        
        if (!$poll) {
            return array(
                'success' => false,
                'error' => 'Poll not found'
            );
        }
        
        if (!$poll['active']) {
            return array(
                'success' => false,
                'error' => 'Poll is closed'
            );
        }
        
        // Check if option index is valid
        if ($optionIndex < 0 || $optionIndex >= count($poll['options'])) {
            return array(
                'success' => false,
                'error' => 'Invalid option'
            );
        }
        
        // Update or add vote
        $poll['votes'][$username] = $optionIndex;
        
        // Update poll in array
        $polls[$pollIndex] = $poll;
        
        // Save to state
        $this->storage->updateRoomState($roomId, array(
            'polls' => $polls
        ));
        
        // Broadcast vote_submitted event
        $this->eventBus->emit($roomId, 'vote_submitted', array(
            'pollId' => $pollId,
            'username' => $username,
            'optionIndex' => $optionIndex
        ));
        
        return array(
            'success' => true,
            'poll' => $poll
        );
    }
    
    // Close a poll
    public function closePoll($roomId, $pollId, $username) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Get current state
        $state = $this->storage->getRoomState($roomId);
        $polls = isset($state['polls']) ? $state['polls'] : array();
        
        // Find and close the poll
        $pollIndex = -1;
        $poll = null;
        for ($i = 0; $i < count($polls); $i++) {
            if ($polls[$i]['id'] === $pollId) {
                $pollIndex = $i;
                $polls[$i]['active'] = false;
                $poll = $polls[$i];
                break;
            }
        }
        
        if (!$poll) {
            return array(
                'success' => false,
                'error' => 'Poll not found'
            );
        }
        
        // Save to state
        $this->storage->updateRoomState($roomId, array(
            'polls' => $polls
        ));
        
        // Broadcast poll_closed event
        $this->eventBus->emit($roomId, 'poll_closed', array(
            'pollId' => $pollId,
            'closedBy' => $username
        ));
        
        return array(
            'success' => true,
            'poll' => $poll
        );
    }
    
    // Get all polls
    public function getPolls($roomId) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        $state = $this->storage->getRoomState($roomId);
        $polls = isset($state['polls']) ? $state['polls'] : array();
        
        return array(
            'success' => true,
            'polls' => $polls
        );
    }
    
    // Delete a poll
    public function deletePoll($roomId, $pollId, $username) {
        if (!$this->storage->roomExists($roomId)) {
            return array(
                'success' => false,
                'error' => 'Room not found'
            );
        }
        
        // Get current state
        $state = $this->storage->getRoomState($roomId);
        $polls = isset($state['polls']) ? $state['polls'] : array();
        
        // Filter out the poll
        $newPolls = array();
        foreach ($polls as $poll) {
            if ($poll['id'] !== $pollId) {
                $newPolls[] = $poll;
            }
        }
        
        // Save to state
        $this->storage->updateRoomState($roomId, array(
            'polls' => $newPolls
        ));
        
        // Broadcast poll_deleted event
        $this->eventBus->emit($roomId, 'poll_deleted', array(
            'pollId' => $pollId,
            'deletedBy' => $username
        ));
        
        return array(
            'success' => true
        );
    }
}