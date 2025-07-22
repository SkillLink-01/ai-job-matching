<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$type = $data['type'];

$collection = $type === 'Employer' ? $db->Employers : $db->Jobseekers;

$collection->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($id)],
    ['$set' => ['status' => 'inactive']]
);

// Optional: log admin action
$db->Admin_log->insertOne([
    'action' => "Deactivated $type with ID $id",
    'user' => 'admin',
    'timestamp' => new MongoDB\BSON\UTCDateTime()
]);

echo json_encode(['success' => true]);
?>
