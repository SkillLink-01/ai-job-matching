<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

// Read JSON input
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate the email
if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid or missing email."
    ]);
    exit;
}

$email = trim($data['email']);

try {
    $db = getMongoDB();

    // Check both employers and jobseekers collections
    $foundInEmployer = $db->employers->findOne(['email' => $email]);
    $foundInJobseeker = $db->jobseekers->findOne(['email' => $email]);

    if ($foundInEmployer || $foundInJobseeker) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Email not found."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}
