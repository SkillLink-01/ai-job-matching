<?php
require 'db_connect.php';

$collection = $db->Notifications;

$notifications = $collection->find(
    ['recipient_type' => 'admin'],
    ['sort' => ['timestamp' => -1]]
);

$response = [];
foreach ($notifications as $note) {
    $response[] = [
        'message' => $note['message'],
        'timestamp' => $note['timestamp']->toDateTime()->format('Y-m-d H:i:s')
    ];
}

echo json_encode($response);
?>
