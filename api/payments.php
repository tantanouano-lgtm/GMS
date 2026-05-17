<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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

// GET - fetch all payments
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT payments.*, members.fullname 
        FROM payments 
        LEFT JOIN members ON payments.member = members.id
        ORDER BY payments.created_at DESC");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($results);
}

// POST - add payment
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $plans = [
        'Regular' => 300,
        'Premium' => 500,
        'VIP'     => 800,
    ];

    if (!isset($data['type']) || !array_key_exists($data['type'], $plans)) {
        echo json_encode(["error" => "Invalid plan selected"]);
        exit;
    }

    $total  = $plans[$data['type']];
    $amount = $total;

    $stmt = $pdo->prepare("INSERT INTO payments 
        (member, type, amount, total) 
        VALUES (:member, :type, :amount, :total)");
    $stmt->execute([
        ':member' => $data['member'],
        ':type'   => $data['type'],
        ':amount' => $amount,
        ':total'  => $total,
    ]);

    $paymentId = $pdo->lastInsertId();
    echo json_encode(["success" => true, "payment_id" => $paymentId]);
}

else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>