<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$host = "localhost";
$db   = "gms_db";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

// Monthly earnings
$stmt = $pdo->query("SELECT SUM(total) AS monthlyEarnings 
    FROM payments WHERE MONTH(created_at) = MONTH(CURRENT_DATE())");
$monthlyEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['monthlyEarnings'] ?? 0;

// Yearly earnings
$stmt = $pdo->query("SELECT SUM(total) AS yearlyEarnings 
    FROM payments WHERE YEAR(created_at) = YEAR(CURRENT_DATE())");
$yearlyEarnings = $stmt->fetch(PDO::FETCH_ASSOC)['yearlyEarnings'] ?? 0;

// Total active members
$stmt = $pdo->query("SELECT COUNT(*) AS totalActiveMembers 
    FROM members WHERE DATE_ADD(start_date, INTERVAL 1 MONTH) > CURDATE()");
$totalActiveMembers = $stmt->fetch(PDO::FETCH_ASSOC)['totalActiveMembers'] ?? 0;

// Total members
$stmt = $pdo->query("SELECT COUNT(*) AS totalMembers FROM members");
$totalMembers = $stmt->fetch(PDO::FETCH_ASSOC)['totalMembers'] ?? 0;

// Yearly chart data
$stmt = $pdo->query("SELECT YEAR(created_at) AS year, SUM(total) AS total_sales
    FROM payments
    WHERE YEAR(created_at) = YEAR(CURRENT_TIMESTAMP)
    GROUP BY YEAR(created_at)
    ORDER BY YEAR(created_at)");
$yearlyChart = ['labels' => [], 'data' => []];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $yearlyChart['labels'][] = $row['year'];
    $yearlyChart['data'][]   = $row['total_sales'];
}

// Monthly chart data
$stmt = $pdo->query("SELECT YEAR(created_at) AS year, MONTH(created_at) AS month, SUM(total) AS total_sales
    FROM payments
    WHERE MONTH(created_at) = MONTH(CURRENT_TIMESTAMP)
    GROUP BY YEAR(created_at), MONTH(created_at)
    ORDER BY YEAR(created_at), MONTH(created_at)");
$monthlyChart = ['labels' => [], 'data' => []];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthName = date("M", mktime(0, 0, 0, $row['month'], 10));
    $monthlyChart['labels'][] = $monthName . ' ' . $row['year'];
    $monthlyChart['data'][]   = $row['total_sales'];
}

// Gender pie chart data
$stmt = $pdo->query("SELECT 
    COUNT(CASE WHEN sex = 'Male' THEN 1 END) AS male_count,
    COUNT(CASE WHEN sex = 'Female' THEN 1 END) AS female_count,
    COUNT(CASE WHEN sex = 'Referral' THEN 1 END) AS referral_count
    FROM members");
$gender = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    'monthlyEarnings'    => $monthlyEarnings,
    'yearlyEarnings'     => $yearlyEarnings,
    'totalActiveMembers' => $totalActiveMembers,
    'totalMembers'       => $totalMembers,
    'yearlyChart'        => $yearlyChart,
    'monthlyChart'       => $monthlyChart,
    'gender'             => $gender,
]);
?>