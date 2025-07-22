<?php
// backend/jobseeker/store_recommendations.php

declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) session_start();

/* ---------- CORS + JSON headers ---------- */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8000');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ---------- Auth guard ---------- */
if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'jobseeker') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $jobseekerId = $_SESSION['user_id'];
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || empty($input['resume_text'])) {
        echo json_encode(['success' => false, 'error' => 'Missing resume_text']);
        exit;
    }

    $resumeText = $input['resume_text'];

    // 1. Send to Flask API
    $flaskUrl = 'http://localhost:5000/match_job';
    $flaskPayload = json_encode([
        'jobseeker_id' => $jobseekerId,
        'resume_text'  => $resumeText
    ]);

    $ch = curl_init($flaskUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $flaskPayload,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json']
    ]);

    $flaskResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        echo json_encode(['success' => false, 'error' => 'Flask API error']);
        exit;
    }

    $responseData = json_decode($flaskResponse, true);
    if (!$responseData['success'] || empty($responseData['matches'])) {
        echo json_encode(['success' => false, 'error' => 'No matches received']);
        exit;
    }

    // 2. Save matches to MongoDB
    require_once __DIR__ . '/db_connect.php';
    $db = getMongoDB();
    $collection = $db->Recommendation_History;

    // Clear old recommendations for this user
    $collection->deleteMany(['jobseeker_id' => $jobseekerId]);

    // Insert new matches
    $matchesToInsert = [];
    foreach ($responseData['matches'] as $match) {
        $matchesToInsert[] = [
            'jobseeker_id' => $jobseekerId,
            'job_id'       => $match['job_id'],
            'score'        => $match['match_score'],
            'resume_score' => $match['resume_score'] ?? null,
            'rule_score'   => $match['rule_score'] ?? null,
            'created_at'   => new MongoDB\BSON\UTCDateTime()
        ];
    }

    if (!empty($matchesToInsert)) {
        $collection->insertMany($matchesToInsert);
    }

    echo json_encode(['success' => true, 'stored_count' => count($matchesToInsert)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
