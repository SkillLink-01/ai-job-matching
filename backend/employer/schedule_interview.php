<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$jobseekerId = $data['jobseeker_id'] ?? null;
$jobTitle = $data['job_title'] ?? '';
$interviewDate = $data['interview_date'] ?? '';

if (!$jobseekerId || !$jobTitle || !$interviewDate) {
    echo json_encode(['status' => 'error', 'message' => 'Missing fields']);
    exit;
}

// Normally here you'd store the interview in the DB and update application status

// Simulate sending a notification
$notification = [
    'to' => $jobseekerId,
    'message' => "You have been scheduled for an interview for: $jobTitle on $interviewDate"
];

// Append to a simple JSON file for simulation
$existing = json_decode(file_get_contents('notifications.json'), true);
$existing[] = $notification;
file_put_contents('notifications.json', json_encode($existing, JSON_PRETTY_PRINT));

echo json_encode(['status' => 'success', 'message' => 'Interview scheduled and notification sent']);
