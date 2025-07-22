<?php
session_start();

// CORS Headers
$allowedOrigin = 'http://localhost:8000';
if (isset($_SERVER['HTTP_ORIGIN']) && $_SERVER['HTTP_ORIGIN'] === $allowedOrigin) {
    header("Access-Control-Allow-Origin: $allowedOrigin");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header('Content-Type: application/json');

// ✅ Session check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'employer') {
    echo json_encode(['error' => 'Unauthorized: Employer not logged in.']);
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB();

try {
    $employerId = $_SESSION['user_id'];

    // ✅ Try matching jobs by ObjectId or string
    $jobs = $db->Jobs->find([
        '$or' => [
            ['employer_id' => new MongoDB\BSON\ObjectId($employerId)],
            ['employer_id' => $employerId]
        ]
    ]);

    $results = [];

    foreach ($jobs as $job) {
        $jobId = $job['_id'];
        $jobTitle = $job['title'] ?? 'Untitled';

        $apps = $db->applications->find(['job_id' => $jobId]);

        foreach ($apps as $app) {
            $jobseekerId = $app['user_id'] ?? null;
            if (!$jobseekerId) continue;

            $jobseeker = $db->Jobseekers->findOne(['_id' => $jobseekerId]);
            if (!$jobseeker) continue;

            $results[] = [
  'app_id' => (string)$app['_id'],
  'job_id' => (string)$job['_id'],
  'jobseeker_id' => (string)$jobseeker['_id'],
  'job_title' => $job['title'],
  'applicant_name' => $jobseeker['full_name'],
  'email' => $jobseeker['email'],
  //'phone' => $jobseeker['phone'],
  'resume' => $app['resume'],
  'cover_letter' => $app['cover_letter'],
  'applied_at' => $app['applied_at']->toDateTime()->format('Y-m-d H:i'),
  'skills' => $jobseeker['skills'] ?? []
]
;
        }
    }

    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
