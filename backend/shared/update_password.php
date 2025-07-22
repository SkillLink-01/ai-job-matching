<?php
header('Content-Type: application/json');
require_once __DIR__ . '/db_connect.php';

// Read JSON body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate input
if (
    !isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL) ||
    !isset($data['new_password']) || strlen($data['new_password']) < 6
) {
    echo json_encode([
        "success" => false,
        "error" => "Invalid input. Email must be valid and password at least 6 characters."
    ]);
    exit;
}

$email = trim($data['email']);
$new_password = $data['new_password'];
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT); // Secure hash

try {
    $db = getMongoDB(); // Connect to MongoDB

    // Try to update in employers collection
    $updateEmployer = $db->employers->updateOne(
        ['email' => $email],
        ['$set' => ['password' => $hashed_password]]
    );

    // If not found, try in jobseekers collection
    if ($updateEmployer->getModifiedCount() === 0) {
        $updateJobseeker = $db->jobseekers->updateOne(
            ['email' => $email],
            ['$set' => ['password' => $hashed_password]]
        );
    }

    if (
        (isset($updateEmployer) && $updateEmployer->getModifiedCount() > 0) ||
        (isset($updateJobseeker) && $updateJobseeker->getModifiedCount() > 0)
    ) {
        echo json_encode([
            "success" => true,
            "message" => "Password updated successfully."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "error" => "Email not found or password unchanged."
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "error" => "Server error: " . $e->getMessage()
    ]);
}
