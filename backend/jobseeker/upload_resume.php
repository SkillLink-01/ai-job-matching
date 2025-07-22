<?php
header("Content-Type: application/json");
require '../db_connect.php';
require '../auth_middleware.php';

$db = getMongoDB();
$collection = $db->Jobseekers;

$jobseeker_id = $_SESSION['jobseeker_id'] ?? null;
if (!$jobseeker_id) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check file upload
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Resume upload failed']);
    exit;
}

$allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
if (!in_array($_FILES['resume']['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

$uploadDir = '../../uploads/';
$filename = basename($_FILES['resume']['name']);
$uniqueName = time() . '_' . $filename;
$uploadPath = $uploadDir . $uniqueName;

if (move_uploaded_file($_FILES['resume']['tmp_name'], $uploadPath)) {
    $collection->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($jobseeker_id)],
        ['$set' => ['resume' => 'uploads/' . $uniqueName]]
    );
    echo json_encode(['message' => 'Resume uploaded successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Could not move uploaded file']);
}
