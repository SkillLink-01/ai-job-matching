<?php
$employer_id = "emp123"; // Replace with session-based ID later
$applications = file_exists('applications.json') ? json_decode(file_get_contents('applications.json'), true) : [];
$jobs = file_exists('jobs.json') ? json_decode(file_get_contents('jobs.json'), true) : [];

$job_map = [];
foreach ($jobs as $job) {
    if ($job['employer_id'] === $employer_id) {
        $job_map[$job['id']] = $job['title'];
    }
}

$applicants = [];
foreach ($applications as $app) {
    if (isset($job_map[$app['job_id']])) {
        $applicants[] = [
            'job_title' => $job_map[$app['job_id']],
            'jobseeker_id' => $app['jobseeker_id'],
            'resume_url' => $app['resume_url'] ?? '',
            'status' => $app['status']
        ];
    }
}

echo json_encode($applicants);
?>
