<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['jobseeker_id'])) {
    echo json_encode([
        'success' => true,
        'jobseeker_id' => $_SESSION['jobseeker_id']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
}
?>
