<?php
/* -----------------------------------------------------------
   backend/shared/login.php  – JSON login endpoint
   -----------------------------------------------------------
   • Accepts a JSON body:  { "email": "...", "password": "..." }
   • Returns JSON: { success:true|false, role, message|error }
   -----------------------------------------------------------*/

/* ========== 1)  CORS & JSON headers ===================== */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

/* ---------- Pre‑flight (OPTIONS) ------------------------ */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

/* ========== 2)  Session settings (cross‑origin safe) ==== */
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => 'localhost',
    'secure'   => false,     // use true only when you switch to HTTPS
    'httponly' => false,
    'samesite' => 'None'     // needed because frontend = http://localhost:8000
]);
session_start();

/* ========== 3)  Read & validate JSON body =============== */
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$email    = trim($data['email']    ?? '');
$password = trim($data['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode(['success' => false, 'error' => 'Email and password are required']);
    exit;
}

/* ========== 4)  DB look‑ups ============================= */
require_once __DIR__ . '/db_connect.php';
$db = getMongoDB();

try {
    /* --- (a)  Jobseeker --------------------------------- */
    $js = $db->Jobseekers->findOne(['email' => $email]);

    if ($js && password_verify($password, $js['password'])) {
        $_SESSION['user_id']      = (string) $js['_id'];
        $_SESSION['role']         = 'jobseeker';
        $_SESSION['full_name']    = $js['full_name'];

        /* you can keep a second id key if other code relies on it */
        $_SESSION['jobseeker_id'] = $_SESSION['user_id'];

        echo json_encode([
            'success' => true,
            'role'    => 'jobseeker',
            'message' => "Welcome, {$js['full_name']}!"
        ]);
        exit;
    }

    /* --- (b)  Employer ---------------------------------- */
    $emp = $db->employers->findOne(['email' => $email]);

    if ($emp && password_verify($password, $emp['password'])) {
        $_SESSION['user_id']     = (string) $emp['_id'];
        $_SESSION['role']        = 'employer';
        $_SESSION['company_name']= $emp['company_name'];
        $_SESSION['employer_id'] = $_SESSION['user_id'];   // convenience

        echo json_encode([
            'success' => true,
            'role'    => 'employer',
            'message' => "Welcome, {$emp['company_name']}!"
        ]);
        exit;
    }
   /* --- (c)  Admin ---------------------------------- */
$admin = $db->admin->findOne(['email' => $email]);

if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['user_id']   = (string) $admin['_id'];
    $_SESSION['role']      = 'admin';
    $_SESSION['admin_name'] = $admin['name']; // Optional if you stored it

    echo json_encode([
        'success' => true,
        'role'    => 'admin',
        'message' => "Welcome Admin!"
    ]);
    exit;
}


    /* --- (d)  No match ---------------------------------- */
    echo json_encode(['success' => false, 'error' => 'Invalid credentials']);

} catch (Throwable $e) {
    // NEVER leak internal errors in production – log them instead
    error_log('Login error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
