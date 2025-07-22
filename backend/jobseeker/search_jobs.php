<?php
header("Content-Type: application/json");
require '../db_connect.php';

$db = getMongoDB();
$collection = $db->Jobs;

$filters = [];

if (!empty($_POST['title'])) {
  $filters['title'] = new MongoDB\BSON\Regex($_POST['title'], 'i');
}
if (!empty($_POST['location'])) {
  $filters['location'] = new MongoDB\BSON\Regex($_POST['location'], 'i');
}
if (!empty($_POST['job_type'])) {
  $filters['job_type'] = $_POST['job_type'];
}

$jobs = $collection->find($filters);
$response = [];

foreach ($jobs as $job) {
  $job['_id'] = (string)$job['_id'];
  $response[] = $job;
}

echo json_encode($response);
