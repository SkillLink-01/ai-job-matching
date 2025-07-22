<?php
$application_id = $_POST['application_id'] ?? '';
$offer = $_POST['offer'] ?? '';

if (!$application_id || !$offer) {
    echo "Missing application ID or offer details.";
    exit;
}

$applications = file_exists('applications.json') ? json_decode(file_get_contents('applications.json'), true) : [];

$found = false;

foreach ($applications as &$app) {
    if ($app['id'] === $application_id) {
        $app['job_offer'] = $offer;
        $app['status'] = 'offer_sent';
        $found = true;
        break;
    }
}

if ($found) {
    file_put_contents('applications.json', json_encode($applications, JSON_PRETTY_PRINT));
    echo "Job offer successfully sent.";
} else {
    echo "Application not found.";
}
?>
