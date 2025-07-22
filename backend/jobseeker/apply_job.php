<?php
ob_start();
session_start();

// 🔧 Debug mode
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 🔍 Log for debugging
file_put_contents(__DIR__ . '/debug_session.txt', json_encode([
    'session' => $_SESSION,
    'post' => $_POST,
    'files' => $_FILES
], JSON_PRETTY_PRINT));

// ✅ CORS Headers
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

// ✅ Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    ob_end_clean();
    exit;
}

// ✅ Session and Role Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'jobseeker') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Login as jobseeker first']);
    ob_end_flush();
    exit;
}

// ✅ Ensure POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    ob_end_flush();
    exit;
}

require_once 'db_connect.php';
$db = getMongoDB();

// ✅ Inputs
$job_id        = $_POST['job_id'] ?? null;
$firstName     = trim($_POST['first_name'] ?? '');
$lastName      = trim($_POST['last_name'] ?? '');
$email         = trim($_POST['email'] ?? '');
$phone         = trim($_POST['phone'] ?? '');
$coverLetter   = trim($_POST['cover_letter'] ?? '');
$customAnswers = $_POST['custom_answers'] ?? [];

if (!$job_id || !preg_match('/^[a-f\d]{24}$/i', $job_id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid Job ID']);
    ob_end_flush();
    exit;
}

if (!$firstName || !$lastName || !$email || !$phone) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    ob_end_flush();
    exit;
}

// ✅ Fetch Job Info
$jobObjId = new MongoDB\BSON\ObjectId($job_id);
$job = $db->Jobs->findOne(['_id' => $jobObjId]);

if (!$job) {
    echo json_encode(['success' => false, 'message' => 'Job not found']);
    ob_end_flush();
    exit;
}

$companyName = $job['company'] ?? 'Unknown Company';
$jobType     = $job['job_type'] ?? 'Unknown Type';
$location    = $job['location'] ?? 'Unknown Location';
$jobTitle    = $job['title'] ?? 'Untitled Job'; // ✅ Extract job title

// ✅ Prevent Duplicate
$applications = $db->applications;
$existing = $applications->findOne([
    'job_id' => $jobObjId,
    'user_id' => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
]);

if ($existing) {
    echo json_encode(['success' => false, 'message' => 'You have already applied for this job']);
    ob_end_flush();
    exit;
}

// ✅ Handle Resume Upload
$resumePath = null;
if (!empty($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'doc', 'docx'];

    if (!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => 'Only PDF, DOC, or DOCX files allowed']);
        ob_end_flush();
        exit;
    }

    $uploadDir = __DIR__ . '/uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = uniqid('resume_', true) . '.' . $ext;
    $filePath = $uploadDir . $fileName;

    if (!move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload resume']);
        ob_end_flush();
        exit;
    }

    $resumePath = 'uploads/resumes/' . $fileName;
}

// ✅ Final Application Object
$application = [
    'job_id'        => $jobObjId,
    'user_id'       => new MongoDB\BSON\ObjectId($_SESSION['user_id']),
    'first_name'    => $firstName,
    'last_name'     => $lastName,
    'email'         => $email,
    'phone'         => $phone,
    'cover_letter'  => $coverLetter,
    'resume'        => $resumePath,
    'custom_answers'=> is_array($customAnswers) ? $customAnswers : [],
    'status'        => 'Applied',
    'applied_at'    => new MongoDB\BSON\UTCDateTime(),

    // 🎯 Additional Job Fields
    'company_name'  => $companyName,
    'job_type'      => $jobType,
    'location'      => $location,
    'job_title'     => $jobTitle
];

// ✅ Save Application
try {
    $insertResult = $applications->insertOne($application);
    file_put_contents(__DIR__ . '/debug_applied_insert.txt', json_encode($application, JSON_PRETTY_PRINT));

    echo json_encode([
        'success' => $insertResult->getInsertedCount() === 1,
        'message' => 'Application submitted successfully'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

ob_end_flush();
?>
