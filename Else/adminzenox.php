<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

define('VIDEO_FILE', 'video_id.txt');
define('DEFAULT_VIDEO_ID', '2pv_s6ki1nA');
define('MAX_VIDEO_ID_LENGTH', 50);


function sanitizeVideoId($videoId) {
    $videoId = trim($videoId);
    
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $videoId)) {
        return false;
    }
    
    if (strlen($videoId) > MAX_VIDEO_ID_LENGTH) {
        return false;
    }
    
    return $videoId;
}


function getVideoId() {
    if (!file_exists(VIDEO_FILE)) {
        return DEFAULT_VIDEO_ID;
    }
    
    $videoId = file_get_contents(VIDEO_FILE);
    $videoId = trim($videoId);
    
    if (empty($videoId)) {
        return DEFAULT_VIDEO_ID;
    }
    
    return $videoId;
}

function saveVideoId($videoId) {
    $sanitized = sanitizeVideoId($videoId);
    
    if ($sanitized === false) {
        return false;
    }
    
    $result = file_put_contents(VIDEO_FILE, $sanitized);
    
    if ($result === false) {
        return false;
    }
    
    return true;
}

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'get':
        $videoId = getVideoId();
        echo json_encode([
            'success' => true,
            'videoId' => $videoId
        ]);
        break;
        
    case 'update':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Only POST method allowed for updates'
            ]);
            break;
        }
        
        $videoId = isset($_POST['videoId']) ? $_POST['videoId'] : '';
        
        if (empty($videoId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Video ID is required'
            ]);
            break;
        }
        
        if (saveVideoId($videoId)) {
            echo json_encode([
                'success' => true,
                'message' => 'Video ID updated successfully',
                'videoId' => sanitizeVideoId($videoId)
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Invalid video ID format or unable to save'
            ]);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Use ?action=get or POST with action=update'
        ]);
        break;
}
?>
