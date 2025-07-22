<?php
session_start();
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

$admin = $db->Admins->findOne(['username' => $username]);

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = (string)$admin['_id'];
    $_SESSION['admin_username'] = $admin['username'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
}
?>
