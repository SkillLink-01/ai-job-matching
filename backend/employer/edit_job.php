<?php
session_start();

header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header('Content-Type: text/plain');

header('Content-Type: application/json');

if (!isset($_SESSION['employer_id'])) {
    echo "Unauthorized.";
    exit;
}

require_once 'db_connect.php';  // Adjust path as needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';

    $db = getMongoDB();
    $collection = $db->Jobs;

    $filter = [
        '_id' => new MongoDB\BSON\ObjectId($id),
        'employer_id' => $_SESSION['employer_id']
    ];

    $update = [
        '$set' => [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'location' => $_POST['location'] ?? '',
            'job_type' => $_POST['job_type'] ?? '',
            'updated_at' => new MongoDB\BSON\UTCDateTime()
        ]
    ];

    $result = $collection->updateOne($filter, $update);

    if ($result->getModifiedCount() > 0) {
        echo "Job updated successfully.";
    } else {
        echo "Job not found or unauthorized.";
    }
}
?>
