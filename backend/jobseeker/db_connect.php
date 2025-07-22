<?php
require_once __DIR__ . '/../../vendor/autoload.php'; // Go up 2 levels to access vendor

function getMongoDB() {
    try {
        $client = new MongoDB\Client("mongodb://localhost:27017");
        return $client->job_matching_system;
    } catch (Exception $e) {
        die("MongoDB Connection Error: " . $e->getMessage());
    }
}
?>
