<?php
header("Content-Type: application/json");
require '../db_connect.php';
require '../auth_middleware.php';

$db = getMongoDB();
$collection = $db->Jobseekers;

$jobseeker_id = $_SESSION['jobseeker_id'] ?? null;
if (!$jobseeker_id) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user = $collection->findOne(['_id' => new MongoDB\BSON\ObjectId($jobseeker_id)]);
if ($user && isset($user['resume'])) {
    echo json_encode(['resume' => $user['resume']]);
} else {
    echo json_encode(['resume' => null]);
}
