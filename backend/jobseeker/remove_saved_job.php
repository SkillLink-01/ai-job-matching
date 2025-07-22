<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Auth check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB();

$jobId = $_POST['job_id'] ?? null;
if (!$jobId || !preg_match('/^[a-f\d]{24}$/i', $jobId)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Job ID']);
    exit;
}

$result = $db->saved_jobs->deleteOne([
    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
    'job_id' => new MongoDB\BSON\ObjectId($jobId)
]);

if ($result->getDeletedCount() > 0) {
    echo json_encode(['status' => 'success', 'message' => 'Job removed from saved list']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Job not found or not removed']);
}
