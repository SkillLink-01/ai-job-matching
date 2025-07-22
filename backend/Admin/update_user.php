<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$id = $data['id'];
$type = $data['type']; // 'Jobseeker' or 'Employer'
$update = [];

if (!empty($data['name'])) {
    $update['name'] = $data['name'];
}
if (!empty($data['email'])) {
    $update['email'] = $data['email'];
}

if ($type === 'Employer') {
    if (isset($update['name'])) {
        $update['company_name'] = $update['name'];
        unset($update['name']);
    }
    $collection = $db->Employers;
} else {
    $collection = $db->Jobseekers;
}

$collection->updateOne(
    ['_id' => new MongoDB\BSON\ObjectId($id)],
    ['$set' => $update]
);

echo json_encode(['success' => true]);
?>
