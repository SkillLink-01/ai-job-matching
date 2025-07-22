<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

header('Content-Type: application/json');

// Validate job_id is provided
if (!isset($_GET['job_id'])) {
    echo json_encode(['error' => 'Job ID is required']);
    exit;
}

$job_id = $_GET['job_id'];

// Validate format of job_id
if (!preg_match('/^[a-f\d]{24}$/i', $job_id)) {
    echo json_encode(['error' => 'Invalid Job ID format']);
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB(); // ✅ make sure this returns the database
$jobsCollection = $db->Jobs; // capital "J" if you're using "Jobs" consistently

try {
    $job = $jobsCollection->findOne([
        '_id' => new MongoDB\BSON\ObjectId($job_id)
    ]);

    if ($job) {
        echo json_encode([
            'title' => $job['title'] ?? '',
            'company' => $job['company'] ?? '',
            'location' => $job['location'] ?? '',
            'job_type' => $job['job_type'] ?? '',
            'description' => $job['description'] ?? '',
            'custom_questions' => $job['custom_questions'] ?? []
        ]);
    } else {
        echo json_encode(['error' => 'Job not found']);
    }

} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
