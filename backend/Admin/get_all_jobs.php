<?php
require 'db_connect.php';

$jobs = $db->Jobs->find([], ['sort' => ['created_at' => -1]])->toArray();
$response = [];

foreach ($jobs as $job) {
    $response[] = [
        'id' => (string)$job['_id'],
        'title' => $job['title'] ?? '',
        'company' => $job['company'] ?? '',
        'location' => $job['location'] ?? '',
        'type' => $job['job_type'] ?? '',
        'status' => $job['status'] ?? 'pending'
    ];
}

echo json_encode($response);
?>
