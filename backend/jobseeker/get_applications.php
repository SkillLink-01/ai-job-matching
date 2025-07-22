<?php

session_start();
file_put_contents(__DIR__ . "/debug_session_check.txt", json_encode($_SESSION, JSON_PRETTY_PRINT));

/*file_put_contents(__DIR__ . '/debug_session_get.txt', json_encode([
    'session' => $_SESSION
], JSON_PRETTY_PRINT));*/

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


// ✅ Headers for CORS and JSON
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// ✅ Preflight check
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Check session and role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'jobseeker') {
    echo json_encode([]);
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB();

$applicationsCollection = $db->applications;
$jobsCollection = $db->Jobs;

$userId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

// ✅ Fetch applications
$cursor = $applicationsCollection->find(['user_id' => $userId]);

$applications = [];

foreach ($cursor as $app) {
    $job = null;

    // Try to fetch job details via job_id
    if (!empty($app['job_id'])) {
        try {
            $jobId = $app['job_id'] instanceof MongoDB\BSON\ObjectId
                ? $app['job_id']
                : new MongoDB\BSON\ObjectId((string)$app['job_id']);
            $job = $jobsCollection->findOne(['_id' => $jobId]);
        } catch (Exception $e) {
            $job = null;
        }
    }

    // Use job fields from either Jobs or embedded
    $applications[] = [
        'job_title'       => $job['title']        ?? $app['job_title']       ?? 'Unknown Title',
        'company_name'    => $job['company']      ?? $app['company_name']    ?? 'Unknown Company',
        'job_type'        => $job['job_type']     ?? $app['job_type']        ?? '',
        'location'        => $job['location']     ?? $app['location']        ?? '',
        'job_description' => $job['description']  ?? '',
        'status'          => $app['status']       ?? 'Applied',
        'applied_at'      => isset($app['applied_at']) && $app['applied_at'] instanceof MongoDB\BSON\UTCDateTime
                                ? $app['applied_at']->toDateTime()->format('Y-m-d') : '',
        'cover_letter'    => $app['cover_letter'] ?? '',
        'resume'          => $app['resume']       ?? '',
        'job_id'          => isset($job['_id']) ? (string)$job['_id'] : null
    ];
}

echo json_encode($applications);
