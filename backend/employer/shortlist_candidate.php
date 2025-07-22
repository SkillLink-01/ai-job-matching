<?php
$app_id = $_POST['app_id'] ?? '';
$applications = file_exists('applications.json') ? json_decode(file_get_contents('applications.json'), true) : [];

foreach ($applications as &$app) {
    if ($app['id'] === $app_id) {
        $app['status'] = 'shortlisted';
        file_put_contents('applications.json', json_encode($applications, JSON_PRETTY_PRINT));
        echo "Candidate shortlisted!";
        exit;
    }
}
echo "Application not found.";
?>
