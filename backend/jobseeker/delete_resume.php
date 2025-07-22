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
if (!$user || !isset($user['resume'])) {
    echo json_encode(['message' => 'No resume to delete.']);
    exit;
}

$resumePath = '../' . $user['resume'];
if (file_exists($resumePath)) {
    unlink($resumePath); // delete file from server
}

$collection->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($jobseeker_id)],
    ['$unset' => ['resume' => ""]]
);

echo json_encode(['message' => 'Resume deleted successfully.']);
