<?php
require 'db_connect.php';

$jobseekers = $db->Jobseekers->find()->toArray();
$employers = $db->Employers->find()->toArray();

$response = [];

foreach ($jobseekers as $user) {
    $response[] = [
        'id' => (string)$user['_id'],
        'name' => $user['name'] ?? '',
        'email' => $user['email'] ?? '',
        'type' => 'Jobseeker',
        'status' => $user['status'] ?? 'active'
    ];
}

foreach ($employers as $user) {
    $response[] = [
        'id' => (string)$user['_id'],
        'name' => $user['company_name'] ?? '',
        'email' => $user['email'] ?? '',
        'type' => 'Employer',
        'status' => $user['status'] ?? 'active'
    ];
}

echo json_encode($response);
?>
