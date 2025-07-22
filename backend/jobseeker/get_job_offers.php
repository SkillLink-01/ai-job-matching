<?php
header("Content-Type: application/json");
session_start();
require '../db_connect.php';
require '../auth_middleware.php'; // checks if jobseeker is logged in

$db = getMongoDB();
$collection = $db->Job_Offers;

$jobseeker_id = $_SESSION['jobseeker_id'];

$offers = $collection->find(['jobseeker_id' => $jobseeker_id]);
$response = [];

foreach ($offers as $offer) {
    $response[] = [
        'job_title' => $offer['job_title'] ?? '',
        'company' => $offer['company'] ?? '',
        'message' => $offer['message'] ?? '',
        'status' => $offer['status'] ?? 'Pending',
        'date_sent' => $offer['date_sent'] ?? '',
    ];
}

echo json_encode($response);
