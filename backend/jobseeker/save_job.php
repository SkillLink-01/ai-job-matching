<?php
session_start();
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ✅ Session Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB();
$savedJobs = $db->saved_jobs;

$jobId = $_POST['job_id'] ?? null;
if (!$jobId || !preg_match('/^[a-f\d]{24}$/i', $jobId)) {
    echo json_encode(['status' => 'error', 'message' => 'Job ID missing or invalid']);
    exit;
}

// ✅ Check if already saved
$exists = $savedJobs->findOne([
    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
    'job_id' => new MongoDB\BSON\ObjectId($jobId)
]);

if ($exists) {
    echo json_encode(['status' => 'error', 'message' => 'Job already saved']);
    exit;
}

// ✅ Save the job
$insert = $savedJobs->insertOne([
    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
    'job_id' => new MongoDB\BSON\ObjectId($jobId),
    'saved_at' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(['status' => 'success', 'message' => 'Job saved successfully']);
