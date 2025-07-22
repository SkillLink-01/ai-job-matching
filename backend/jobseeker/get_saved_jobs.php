<?php
session_start();

// ✅ Headers for CORS and JSON
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Role + session check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    echo json_encode([]);
    exit;
}

// ✅ MongoDB Connection
require_once 'db_connect.php';
$db = getMongoDB();
$savedJobsCollection = $db->saved_jobs;
$jobsCollection = $db->Jobs;

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// ✅ Fetch all saved jobs for this user
$savedJobsCursor = $savedJobsCollection->find(['user_id' => $userId]);

$savedJobsList = [];

foreach ($savedJobsCursor as $savedJob) {
    $jobId = $savedJob['job_id'] ?? null;
    if (!$jobId) continue;

    $job = $jobsCollection->findOne(['_id' => $jobId]);
    if ($job) {
        $savedJobsList[] = [
            'title'    => $job['title'] ?? 'Untitled',
            'company'  => $job['company'] ?? 'Unknown',
            'location' => $job['location'] ?? 'N/A',
            'job_type' => $job['job_type'] ?? '',
            '_id'      => (string)$job['_id'],
            'saved_at' => isset($savedJob['saved_at']) ? $savedJob['saved_at']->toDateTime()->format('Y-m-d H:i') : ''
        ];
    }
}

echo json_encode($savedJobsList);
