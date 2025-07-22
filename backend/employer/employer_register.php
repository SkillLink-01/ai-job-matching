<?php
// CORS HEADERS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content
    exit;
}

require 'db_connect.php';

// Validate POST method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

// Parse JSON body
$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data['company_name'], $data['email'], $data['password'], $data['location'], $data['industry'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$company_name = $data['company_name'];
$email = $data['email'];
$password = $data['password'];
$location = $data['location'];
$industry = $data['industry'];

$db = getMongoDB();
$collection = $db->employers;

// Check if email exists
$existing = $collection->findOne(['email' => $email]);
if ($existing) {
    echo json_encode(["error" => "Email already registered"]);
    exit;
}

// Insert employer
$employer = [
    "company_name" => $company_name,
    "email" => $email,
    "password" => password_hash($password, PASSWORD_DEFAULT),
    "location" => $location,
    "industry" => $industry,
    "created_at" => new MongoDB\BSON\UTCDateTime()
];

try {
    $result = $collection->insertOne($employer);
    echo json_encode(["success" => true, "message" => "Employer registered"]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
