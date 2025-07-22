<?php
session_start();
header('Content-Type: application/json');

// Simulate login (replace with real session check in production)
$_SESSION['user_id'] = 'jobseeker123'; // TEMP: simulate logged-in user
$_SESSION['role'] = 'jobseeker';

// Check auth
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    echo json_encode([]);
    exit;
}

$userId = $_SESSION['user_id'];

// Load notifications (simulated via JSON file)
$notifications = json_decode(file_get_contents('notifications.json'), true);

// Filter for current jobseeker
$userNotifications = array_filter($notifications, function($n) use ($userId) {
    return $n['to'] === $userId;
});

echo json_encode(array_values($userNotifications));
