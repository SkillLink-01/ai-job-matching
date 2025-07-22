<?php
require 'db_connect.php';

$collection = $db->Admin_log;

$logs = $collection->find([], ['sort' => ['timestamp' => -1]]);

$response = [];
foreach ($logs as $log) {
    $response[] = [
        'action' => $log['action'],
        'user' => $log['user'],
        'timestamp' => $log['timestamp']->toDateTime()->format('Y-m-d H:i:s')
    ];
}

echo json_encode($response);
?>
