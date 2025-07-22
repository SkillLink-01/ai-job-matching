<?php
$application_id = $_POST['application_id'] ?? '';
$feedback = $_POST['feedback'] ?? '';

if (!$application_id || !$feedback) {
    echo "Missing application ID or feedback.";
    exit;
}

// Load applications
$applications = file_exists('applications.json') ? json_decode(file_get_contents('applications.json'), true) : [];

$found = false;

foreach ($applications as &$app) {
    if ($app['id'] === $application_id) {
        $app['feedback'] = $feedback;
        $app['status'] = 'feedback_sent';
        $found = true;
        break;
    }
}

if ($found) {
    file_put_contents('applications.json', json_encode($applications, JSON_PRETTY_PRINT));
    echo "Feedback successfully sent.";
} else {
    echo "Application not found.";
}
?>
