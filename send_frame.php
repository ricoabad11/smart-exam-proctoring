<?php
// send_frame.php - receives POST frame and relays to Pusher
session_start();
require __DIR__ . '/vendor/autoload.php';

$app_id = getenv('PUSHER_APP_ID');
$app_key = getenv('PUSHER_APP_KEY');
$app_secret = getenv('PUSHER_APP_SECRET');
$app_cluster = getenv('PUSHER_APP_CLUSTER');

if (!$app_id || !$app_key || !$app_secret || !$app_cluster) {
    http_response_code(500);
    echo json_encode(['error'=>'Missing Pusher env vars']);
    exit();
}

$pusher = new Pusher\Pusher(
    $app_key,
    $app_secret,
    $app_id,
    ["cluster" => $app_cluster, "useTLS" => true]
);

// Verify session_id in session (set when student logged in)
if (!isset($_SESSION['session_id'])) {
    http_response_code(403);
    echo json_encode(['error'=>'No session']);
    exit();
}

$student_session = $_SESSION['session_id'];

// Accept either form-data (from camera POST) or JSON
$frame = null;
if (!empty($_POST['frame'])) {
    $frame = $_POST['frame'];
} else {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!empty($body['frame'])) $frame = $body['frame'];
}

if (!$frame) {
    http_response_code(400);
    echo json_encode(['error'=>'No frame']);
    exit();
}

// Trigger Pusher on private-student-{session}
$channel = "private-student-" . $student_session;
$event = "camera-frame";

try {
    $pusher->trigger($channel, $event, ['frame' => $frame]);
    echo json_encode(['status'=>'sent']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
