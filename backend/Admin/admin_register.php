<?php
require 'db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (!$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit;
}

$existing = $db->Admins->findOne(['username' => $username]);
if ($existing) {
    echo json_encode(['success' => false, 'message' => 'Username already exists']);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$db->Admins->insertOne([
    'username' => $username,
    'password' => $hashedPassword
]);

echo json_encode(['success' => true]);
?>
