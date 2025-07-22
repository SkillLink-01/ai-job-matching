<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method"]);
    exit;
}

if (!isset($_POST['email'], $_POST['password'])) {
    echo json_encode(["error" => "Missing email or password"]);
    exit;
}

$email = $_POST['email'];
$password = $_POST['password'];

$db = getMongoDB();
$collection = $db->employers;

$employer = $collection->findOne(['email' => $email]);

if ($employer && password_verify($password, $employer['password'])) {
    $_SESSION['employer_id'] = (string)$employer['_id'];
    $_SESSION['company_name'] = $employer['company_name'];
    echo json_encode(["success" => true, "message" => "Welcome, {$employer['company_name']}!"]);
} else {
    echo json_encode(["error" => "Invalid credentials"]);
}
?>
