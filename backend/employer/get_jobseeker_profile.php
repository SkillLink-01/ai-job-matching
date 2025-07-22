<?php
session_start();

// CORS
header('Access-Control-Allow-Origin: http://localhost:8000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Auth check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../shared/db_connect.php';
$db = getMongoDB();

try {
    if (!isset($_GET['user_id']) || strlen($_GET['user_id']) !== 24) {
        throw new Exception('Invalid user ID');
    }

    $userId = new MongoDB\BSON\ObjectId($_GET['user_id']);
    $jobseeker = $db->Jobseekers->findOne(['_id' => $userId]);

    if (!$jobseeker) {
        throw new Exception('Jobseeker not found');
    }

    echo json_encode([
        'full_name' => $jobseeker['full_name'] ?? '',
        'email' => $jobseeker['email'] ?? '',
        'phone' => $jobseeker['phone'] ?? '',
        'location' => $jobseeker['location'] ?? '',
        'skills' => $jobseeker['skills'] ?? [],
        'experience' => $jobseeker['experience'] ?? [],
        'resume' => $jobseeker['resume'] ?? ''
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
