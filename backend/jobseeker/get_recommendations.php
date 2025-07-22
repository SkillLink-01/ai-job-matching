<?php
declare(strict_types=1);

/* Start session */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* CORS and JSON headers */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8000'); // Adjust if your frontend origin differs
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* Handle preflight */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* Simple auth check */
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'jobseeker') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

/* Check if resume file is uploaded */
if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Resume file is required']);
    exit;
}

/* Load uploaded resume text (assuming plain text for demo) */
$resumeText = file_get_contents($_FILES['resume']['tmp_name']);
if (!$resumeText) {
    echo json_encode(['success' => false, 'error' => 'Failed to read resume file']);
    exit;
}

/* Connect to MongoDB - update connection details as needed */
require_once __DIR__ . '/db_connect.php';
$db = getMongoDB();

/* Load jobs from DB (you can customize your query here) */
$jobsCursor = $db->Jobs->find([], ['projection' => ['_id' => 1, 'title' => 1, 'company' => 1, 'location' => 1, 'description' => 1]]);
$jobs = [];
foreach ($jobsCursor as $job) {
    $jobs[] = [
        'job_id' => (string)$job['_id'],
        'title' => $job['title'] ?? '',
        'company' => $job['company'] ?? '',
        'location' => $job['location'] ?? '',
        'description' => $job['description'] ?? '',
    ];
}

/* Prepare data to send to Flask AI API */
$payload = json_encode([
    'resume_text' => $resumeText,
    'jobs' => $jobs,
]);

/* Call Flask AI API via cURL */
$ch = curl_init('http://localhost:5000/match_resume');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['success' => false, 'error' => 'Failed to call AI API: ' . $err]);
    exit;
}

$aiResponse = json_decode($response, true);
if (!$aiResponse || !isset($aiResponse['matches'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid response from AI API']);
    exit;
}

/* Optional: store matches into Recommendation_History here if you want */

/* Return AI matches to frontend */
echo json_encode([
    'success' => true,
    'data' => $aiResponse['matches']
]);
