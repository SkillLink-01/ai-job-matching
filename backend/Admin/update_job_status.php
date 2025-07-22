<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$jobId = $data['id'];
$newStatus = $data['status']; // "approved", "rejected", or "inactive"

$updateResult = $db->Jobs->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($jobId)],
    ['$set' => ['status' => $newStatus]]
);

// Optional: log admin action
$db->Admin_log->insertOne([
    'action' => "Changed job status to $newStatus for job ID $jobId",
    'user' => 'admin',
    'timestamp' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(['success' => $updateResult->getModifiedCount() > 0]);
?>
