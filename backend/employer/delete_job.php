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
    if (!isset($_SESSION['employer_id'])) {
        throw new Exception('Unauthorized');
    }

    if (!isset($_POST['id']) || !preg_match('/^[a-f\d]{24}$/i', $_POST['id'])) {
        throw new Exception('Invalid job ID');
    }

    $jobId = $_POST['id'];
    $employerId = $_SESSION['employer_id'];

    $db = getMongoDB();
    $collection = $db->Jobs;

    $result = $collection->deleteOne([
        '_id' => new MongoDB\BSON\ObjectId($jobId),
        'employer_id' => $employerId
    ]);

    if ($result->getDeletedCount() === 0) {
        throw new Exception('Job not found or unauthorized');
    }

    echo json_encode(['message' => 'Job deleted successfully']);
} catch (Exception $e) {
    http_response_code(401);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
