<?php
//----------------------------------------------------------------
//  backend/jobseeker/update_profile.php
//----------------------------------------------------------------
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/* ---------- 1.  CORS / JSON  ----------------------------- */
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:8000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD']==='OPTIONS'){ http_response_code(200); exit; }

/* ---------- 2.  Session / role check --------------------- */
session_start();
if (empty($_SESSION['user_id']) || $_SESSION['role']!=='jobseeker') {
    echo json_encode(['success'=>false,'message'=>'Unauthorized']); exit;
}
$jsId = new MongoDB\BSON\ObjectId($_SESSION['user_id']);

/* ---------- 3.  Read JSON body --------------------------- */
$raw = file_get_contents('php://input');
$data= json_decode($raw,true);
if (json_last_error()!==JSON_ERROR_NONE){
    echo json_encode(['success'=>false,'message'=>'Invalid JSON']); exit;
}

/* ---------- 4.  Normalise / validate --------------------- */
$upd = [];

if (!empty($data['full_name']))   $upd['full_name'] = trim($data['full_name']);
if (!empty($data['email']))       $upd['email']     = trim($data['email']);
if (!empty($data['location']))    $upd['location']  = trim($data['location']);
if (!empty($data['job_type']))    $upd['job_type']  = trim($data['job_type']);

/* skills: accept array OR comma‑string -------------------- */
if (!empty($data['skills'])){
    $skills = is_array($data['skills'])
        ? $data['skills']
        : explode(',',$data['skills']);
    $skills = array_values(array_filter(array_map('trim',$skills)));
    $upd['skills'] = $skills;                 // ← always an array
}

/* experience --------------------------------------------- */
if (!empty($data['experience']) && is_array($data['experience'])){
    $upd['experience'] = $data['experience'];
}

/* resume (base64) ---------------------------------------- */
if (!empty($data['resume_base64']) && !empty($data['resume_filename'])){
    $upd['resume'] = [
        'filename'  => $data['resume_filename'],
        'mime_type' => 'application/octet-stream',
        'content'   => $data['resume_base64']
    ];
}

/* ---------- 5.  Update DB -------------------------------- */
require_once __DIR__.'/db_connect.php';
$db = getMongoDB();

try {
    if ($upd) {
        $db->Jobseekers->updateOne(['_id' => $jsId], ['$set' => $upd]);
    }

   // Optional AI trigger — disabled because file is missing
// require_once __DIR__.'/../../AI/utils/flask_helper.php';
// triggerFlaskMatching();

    // Return full updated profile
    $updatedProfile = $db->Jobseekers->findOne(['_id' => $jsId]);
    $updatedProfile['_id'] = (string)$updatedProfile['_id']; // prevent JSON encoding issue
    echo json_encode($updatedProfile);
} catch (Throwable $e) {
    echo json_encode(['success'=>false,'message'=>'Server error: '.$e->getMessage()]);
}
