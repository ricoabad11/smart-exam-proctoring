<?php
// pusher_auth.php - for Render (server-side)
session_start();
header("Content-Type: application/json");

// Allow CORS from your proctor UI domain (adjust)
header('Access-Control-Allow-Origin: *'); // during testing; narrow this in production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

// Channel auth for private channels (called by Pusher JS subscribe)
if (isset($_POST['channel_name']) && isset($_POST['socket_id'])) {
    // Optionally validate session / permissions here
    echo $pusher->socket_auth($_POST['channel_name'], $_POST['socket_id']);
    exit();
}

// Also support inbound POST frame relays from student (optional)
$body = json_decode(file_get_contents('php://input'), true);
if (isset($body['channel'], $body['event'], $body['frame'])) {
    // Trigger the event (server-to-server)
    $pusher->trigger($body['channel'], $body['event'], ['frame' => $body['frame']]);
    echo json_encode(['status'=>'sent']);
    exit();
}

echo json_encode(['status'=>'ok']);
