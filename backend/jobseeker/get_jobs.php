<?php
session_start();

// CORS headers (adjust to match your frontend URL)
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Preflight request handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header('Content-Type: application/json');

require_once 'db_connect.php';

try {
    $db = getMongoDB();
    $collection = $db->Jobs;

    // Fetch all jobs (you can later add filters like job_type, location, etc.)
    $cursor = $collection->find([], [
        'sort' => ['posted_at' => -1] // Newest first
    ]);

    $jobs = [];
    foreach ($cursor as $job) {
        $jobArray = json_decode(json_encode($job), true);
        $jobArray['_id'] = (string)$job['_id'];

        if (isset($job['posted_at']) && $job['posted_at'] instanceof MongoDB\BSON\UTCDateTime) {
            $jobArray['posted_at'] = $job['posted_at']->toDateTime()->format(DateTime::ATOM);
        }

        // Optional defaults to prevent undefined indexes
        $jobArray['title'] = $job['title'] ?? 'N/A';
        $jobArray['description'] = $job['description'] ?? 'N/A';
        $jobArray['category'] = $job['category'] ?? 'N/A';
        $jobArray['location'] = $job['location'] ?? 'N/A';
        $jobArray['job_type'] = $job['job_type'] ?? 'N/A';
        $jobArray['employer_id'] = $job['employer_id'] ?? '';

        $jobs[] = $jobArray;
    }

    echo json_encode($jobs);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error fetching jobs.']);
}
