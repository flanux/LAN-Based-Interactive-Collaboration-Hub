<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ .'/core/room.php';
require_once __DIR__ .'/core/eventbus.php';

$roomManager = new RoomManager();
$eventBus = new EventBus();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

$response = [];

switch ($action) {
    case 'create_room':
        $role = $_POST['role'] ?? 'teacher';
        $response = $roomManager->createRoom($role);
        break;

    case 'join_room':
        $roomId = $_POST['roomId'] ?? null;
        $username = $_POST['username'] ??'Anonymous';
        $role = $_POST['role'] ??'student';

        if ($roomId) {
            $response = [
                'success' => false,
                'error' => 'Room Id is required'
            ];
        } else {
            $response = $roomManager->joinRoom($roomId, $username, $role);
        }
        break;

        case 'get_room':
            $roomId = $_GET['roomId'] ?? null;

            if(!roomId) {
                $response = [
                    'success' => false,
                    'error' => 'Room Id is required'
                ];
            } else {
                $response = $roomManager->getRoomInfo($roomId);
            }
            break;

            case 'poll':
                $room = $_GET['roomId'] ?? null;
                $afterId = intval($_GET['after'] ??0);

                if(!$roomId) {
                    $response = [
                        'success' => false,
                        'error' => 'Room id is required'
                    ];
                } else {
                    $response = $eventBus->poll($roomId, $afterId);
                }
                break;

                case 'emit':
                    $roomId = $_GET['roomId'] ?? null;
                    $eventType = $_POST['type'] ??'';
                    $eventData = json_decode($_POST['data'] ?? null, true); 

                    if(!$roomId || !$eventType) {
                        $response = [
                            'success'=> false,
                            'error' => 'Room id and event type are required'
                        ];
                    } else {
                        $response = $eventBus -> emit($roomId, $eventType, $eventData);
                    }
                    break;

                    default:
                        $response = [
                            'success' => false,
                            'error' => 'Invalid action',
                            'available_actions' => [
                                'create_room',
                                'join_room',
                                'get_room',
                                'poll',
                                'emit',
                            ]
                        ];
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>