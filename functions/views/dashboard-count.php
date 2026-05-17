<?php
$apiUrl   = 'http://localhost/GMS/api/dashboard.php';
$response = file_get_contents($apiUrl);
$data     = json_decode($response, true);

function calculateMonthlyEarnings() {
    global $data;
    return $data['monthlyEarnings'] ?? 0;
}

function calculateYearlyEarnings() {
    global $data;
    return $data['yearlyEarnings'] ?? 0;
}

function countTotalActiveMembers() {
    global $data;
    return $data['totalActiveMembers'] ?? 0;
}

function countTotalMembers() {
    global $data;
    return $data['totalMembers'] ?? 0;
}
?>