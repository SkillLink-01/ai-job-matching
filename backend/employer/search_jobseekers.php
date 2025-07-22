<?php
// Simulating MongoDB with JSON for now
$jobseekers = file_exists('jobseekers.json') ? json_decode(file_get_contents('jobseekers.json'), true) : [];

$skills = isset($_POST['skills']) ? array_map('trim', explode(',', strtolower($_POST['skills']))) : [];
$location = isset($_POST['location']) ? strtolower(trim($_POST['location'])) : '';
$job_type = isset($_POST['job_type']) ? strtolower(trim($_POST['job_type'])) : '';

$results = array_filter($jobseekers, function($seeker) use ($skills, $location, $job_type) {
    $match = true;

    if (!empty($skills)) {
        $seeker_skills = array_map('strtolower', $seeker['skills'] ?? []);
        $match = !array_diff($skills, $seeker_skills);
    }

    if ($match && $location !== '') {
        $match = strtolower($seeker['location'] ?? '') === $location;
    }

    if ($match && $job_type !== '') {
        $match = strtolower($seeker['job_type'] ?? '') === $job_type;
    }

    return $match;
});

echo json_encode(array_values($results));
?>
