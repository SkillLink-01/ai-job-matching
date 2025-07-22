<?php
require 'db_connect.php';

$response = [
    'jobseekers' => $db->Jobseekers->countDocuments(),
    'employers' => $db->Employers->countDocuments(),
    'jobs' => $db->Jobs->countDocuments(),
    'applications' => $db->Applications->countDocuments(),
    'interviews' => $db->Interviews->countDocuments()
];

echo json_encode($response);
?>
