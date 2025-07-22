<?php

// ---------- Set session cookie options (safe for localhost) ----------
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => false,   // ← OK for local HTTP
    'httponly' => true,
    'samesite' => 'Lax'    // ← safer than 'None' for localhost
]);
session_start();

// ---------- CORS + content headers ----------
header("Access-Control-Allow-Origin: http://localhost");  // or http://localhost:8000 if needed
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// ---------- Authorization check ----------
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'jobseeker') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ---------- MongoDB connection ----------
require_once 'db_connect.php';
$db = getMongoDB();
$collection = $db->Jobseekers;

try {
    $jobseekerId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);
    $user = $collection->findOne(['_id' => $jobseekerId]);

    if (!$user) {
        echo json_encode(['error' => 'Profile not found']);
        exit;
    }

    // ---------- Build response ----------
    $response = [
        'full_name'  => $user['full_name'] ?? '',
        'email'      => $user['email'] ?? '',
        'location'   => $user['location'] ?? '',
        'job_type'   => $user['job_type'] ?? '',
        'skills'     => $user['skills'] ?? [],
        'experience' => $user['experience'] ?? []
    ];

    // ---------- Optional: include resume ----------
    if (!empty($user['resume']['content']) && !empty($user['resume']['filename'])) {
        $response['resume'] = [
            'filename' => $user['resume']['filename'],
            'content'  => $user['resume']['content']
        ];
    }

    // ---------- Optional: include profile photo ----------
    if (!empty($user['photo']['content']) && !empty($user['photo']['mime_type'])) {
        $response['photo'] = 'data:' . $user['photo']['mime_type'] . ';base64,' . $user['photo']['content'];
    }

    echo json_encode($response);
} catch (Exception $e) {
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
