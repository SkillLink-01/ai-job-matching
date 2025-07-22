<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

header('Content-Type: application/json');

require_once 'db_connect.php';

if (!isset($_SESSION['employer_id'])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized: Employer not logged in."]);
    exit;
}

$employer_id = $_SESSION['employer_id'];

try {
    $db = getMongoDB();
    $collection = $db->Jobs;

    $cursor = $collection->find(['employer_id' => $employer_id]);

    $jobs = [];
    foreach ($cursor as $job) {
        $jobArray = json_decode(json_encode($job), true);
        $jobArray['_id'] = (string)$job['_id'];

        if (isset($job['posted_at']) && $job['posted_at'] instanceof MongoDB\BSON\UTCDateTime) {
            $jobArray['posted_at'] = $job['posted_at']->toDateTime()->format(DateTime::ATOM);
        }

        $jobs[] = $jobArray;
    }

    echo json_encode($jobs);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Server error: " . $e->getMessage()]);
}
