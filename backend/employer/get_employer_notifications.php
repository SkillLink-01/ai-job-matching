<?php
session_start();

header('Access-Control-Allow-Origin: http://localhost:8000'); // <-- adjust this to your frontend origin
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Simulated login (REMOVE this when you implement real login)
$_SESSION['user_id'] = 'employer456'; 
$_SESSION['role'] = 'employer';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user_id'];

// Make sure notifications.json exists and is valid
$notifications = json_decode(file_get_contents('notifications.json'), true);

$employerNotifications = array_filter($notifications, function($n) use ($userId) {
    return $n['to'] === $userId;
});

echo json_encode(array_values($employerNotifications));
?>
