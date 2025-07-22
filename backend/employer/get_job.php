<?php
ob_start();
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CORS setup
$allowedOrigin = 'http://localhost:8000';
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header('Access-Control-Allow-Origin: ' . $allowedOrigin);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit(0);
}

header('Content-Type: application/json');

try {
    // Check if employer is logged in
    if (!isset($_SESSION['employer_id'])) {
        throw new Exception('Unauthorized: Employer not logged in.');
    }

    // Check if ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Job ID not provided.');
    }

    $jobId = $_GET['id'];

    // Validate ObjectId format (24-character hex string)
    if (!preg_match('/^[a-f\d]{24}$/i', $jobId)) {
        throw new Exception('Invalid job ID format.');
    }

    require_once 'db_connect.php';
    $db = getMongoDB();
    $collection = $db->Jobs;

    // Query using ObjectId
    $job = $collection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($jobId),
        'employer_id' => $_SESSION['employer_id']
    ]);

    if (!$job) {
        throw new Exception('Job not found.');
    }

    $jobArray = [
    '_id' => (string)$job['_id'],
    'title' => $job['title'] ?? '',
    'description' => $job['description'] ?? '',
    'category' => $job['category'] ?? '',
    'location' => $job['location'] ?? '',
    'job_type' => $job['job_type'] ?? '',
    'posted_at' => isset($job['posted_at']) && $job['posted_at'] instanceof MongoDB\BSON\UTCDateTime
        ? $job['posted_at']->toDateTime()->format(DateTime::ATOM)
        : null
];


    if (isset($job['posted_at']) && $job['posted_at'] instanceof MongoDB\BSON\UTCDateTime) {
        $jobArray['posted_at'] = $job['posted_at']->toDateTime()->format(DateTime::ATOM);
    }

    ob_end_clean();
    echo json_encode($jobArray);

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>