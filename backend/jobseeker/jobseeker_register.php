<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once 'db_connect.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        throw new Exception("Invalid input format");
    }

    $full_name = trim($data['full_name'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';

    if (!$full_name || !$email || !$password || !$confirm_password) {
        throw new Exception("All fields are required.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email format.");
    }

    if ($password !== $confirm_password) {
        throw new Exception("Passwords do not match.");
    }

    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters.");
    }

    $db = getMongoDB();
    $collection = $db->Jobseekers;

    // Check if email already exists
    $existing = $collection->findOne(['email' => $email]);
    if ($existing) {
        throw new Exception("Email already registered.");
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $result = $collection->insertOne([
        'full_name' => $full_name,
        'email' => $email,
        'password' => $hashedPassword,
        'skills' => [],
        'experience' => [],
        'created_at' => new MongoDB\BSON\UTCDateTime(),
    ]);

    // Optional: Start session
    $_SESSION['jobseeker_id'] = (string)$result->getInsertedId();

    echo json_encode(['message' => 'Registration successful']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
