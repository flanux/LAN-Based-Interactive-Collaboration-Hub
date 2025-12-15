<?php

class Storage {
    private $dataDir = __DIR__ . '/../data/rooms/';

    public function __construct() {

        if (!file_exists($this->dataDir)){
            mkdir($this->dataDir, 0777, true);
        }
    }

    public function createRoom($roomId) {
        $roomPath = $this->dataDir . $roomId;

        if (file_exists($roomPath)){
            return false;
        } 

        mkdir($roomPath, 0777, true);
        mkdir($roomPath. '/files', 0777, true);

        $initialState = [
            'roomId' => $roomId,
            'created' => time(),
            'participants' => [],
            'notes' => '',
            'polls' => []
        ];

        file_put_contents(
            $roomPath .'/state.json',
            json_encode($initialState, JSON_PRETTY_PRINT)
        );

        $initialEvents = [];
        file_put_contents(
            $roomPath .'/events.json',
            json_encode($initialEvents, JSON_PRETTY_PRINT)
        );

        return true;
    }

    public function roomExists($roomId) {
        return file_exists($this->dataDir. $roomId. '/state.json');
    }

    public function getRoomState($roomId) {
        if (!$this->roomExists($roomId)) {
            return null;
        }

        $statePath = $this->dataDir .$roomId . '/state.json';
        $content = file_get_contents($statePath);    
        return json_decode($content, true);
    }

    public function updateRoomState($roomId, $updates) {
        if(!$this->roomExists( $roomId )) {
            return false;
        }

        $statePath = $this->dataDir . $roomId . '/state.json';
        $state = $this->getRoomState($roomId);

        $state = array_merge( $state, $updates );

        file_put_contents(
            $statePath,
            json_encode($state, JSON_PRETTY_PRINT)
        );

        return true;
    }

    public function getEvents($roomId){
        if (!$this->roomExists($roomId)) {
            return null;
        }

        $eventsPath = $this->dataDir . $roomId . '/events.json';
        $content = file_get_contents($eventsPath);
        return json_decode($content, true);
    }

    public function getEventsAffter($roomId, $afterId){
       $allEvents = $this->getEvents($roomId); 

       if($allEvents === null) {
        return null;
       }

       $filtered = array_filter($allEvents, function($event) use ($afterId) {
            return $event['id'] == $afterId;
       });

       return array_values($filtered);
    }


    public function appendEvent($roomId, $type, $data) {
        if (!this->roomExists($roomId)) {
            return false;
        }

        $eventsPath = $this->dataDir . $roomId . '/events.json';
        $events = $this->getEvents($roomId);

        $newId = count($events) > 0 ? $events[count($events) - 1]['id'] + 1:1;

        $newEvent = [
            'id' => $newId,
            'type'=> $type,
            'data' => $data,
            'timestamp' => time()
        ];

        $events[] = $newEvent;

        file_put_contents(
            $eventsPath,
            json_encode($events, JSON_PRETTY_PRINT)
        );

        return $newEvent;
    }

    public function deleteRoom($roomId,){
        if (!$this->roomExists($roomId)) {
            return false;
        }

        $roomPath = $this->dataDir . $roomId;

        $filesDir = $roomPath .'/files';
        if (file_exists( $filesDir )) {
            $files = scandir( $filesDir );
            foreach ($files as $file) {
                if ($file != '.'&& $file != '..') {
                    unlink( $filesDir .'/'. $file );
                }
            }
            rmdir( $filesDir );
        }

        unlink($roomPath.'/state.json');
        unlink($roomPath.'/events.json');

        rmdir($roomPath);

        return true;
    }
}
?>