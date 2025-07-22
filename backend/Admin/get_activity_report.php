<?php
require 'db_connect.php';

$jobseekerActivity = $db->Jobseekers->aggregate([
    ['$project' => [
        'name' => 1,
        'applications_count' => ['$size' => ['$ifNull' => ['$applied_jobs', []]]]
    ]],
    ['$sort' => ['applications_count' => -1]],
    ['$limit' => 10]
]);

$jobActivity = $db->Jobs->aggregate([
    ['$project' => [
        'title' => 1,
        'company' => 1,
        'applications' => 1,
        'applications_count' => ['$size' => ['$ifNull' => ['$applications', []]]]
    ]],
    ['$sort' => ['applications_count' => -1]],
    ['$limit' => 10]
]);

$response = [
    'top_jobseekers' => [],
    'top_jobs' => []
];

foreach ($jobseekerActivity as $js) {
    $response['top_jobseekers'][] = [
        'name' => $js['name'] ?? 'Unnamed',
        'applications_count' => $js['applications_count']
    ];
}

foreach ($jobActivity as $job) {
    $response['top_jobs'][] = [
        'title' => $job['title'],
        'company' => $job['company'],
        'applications_count' => $job['applications_count']
    ];
}

echo json_encode($response);
?>
