<?php
require_once 'db_connect.php'; // Ensure this file sets $db

header('Content-Type: application/json');

// Validate required fields
$requiredFields = ['title', 'description', 'category', 'location', 'job_type', 'company', 'employer_id', 'required_skills'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field])) {
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit;
    }
}

$title = $_POST['title'];
$description = $_POST['description'];
$category = $_POST['category'];
$location = $_POST['location'];
$job_type = $_POST['job_type'];
$company = $_POST['company'];
$logo = $_POST['logo'] ?? '';
$employer_id = $_POST['employer_id'];
$required_skills = isset($_POST['required_skills']) ? explode(',', $_POST['required_skills']) : [];

try {
    // ✅ Select the correct MongoDB collection
    $jobsCollection = $db->Jobs;

    $jobData = [
        'title' => $title,
        'description' => $description,
        'category' => $category,
        'location' => $location,
        'job_type' => $job_type,
        'company' => $company,
        'logo' => $logo,
        'posted_at' => new MongoDB\BSON\UTCDateTime(),
        'employer_id' => $employer_id,
        'required_skills' => $required_skills
    ];

    $result = $jobsCollection->insertOne($jobData);

    echo json_encode(['success' => true, 'message' => 'Job posted successfully', 'job_id' => (string)$result->getInsertedId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error posting job: ' . $e->getMessage()]);
}
?>
