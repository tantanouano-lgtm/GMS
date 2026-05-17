<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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

$method = $_SERVER['REQUEST_METHOD'];

// GET - fetch all members
if ($method === 'GET') {
    $stmt    = $pdo->query("SELECT * FROM members");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($members);
}

// POST - add member or register member
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }

    // Registration flow - has email and password
    if (isset($data['email'])) {
        if ($data['password'] !== $data['confirm_password']) {
            echo json_encode(["error" => "Passwords do not match"]);
            exit;
        }
        $check = $pdo->prepare("SELECT id FROM members WHERE email = ?");
        $check->execute([$data['email']]);
        if ($check->fetch()) {
            echo json_encode(["error" => "Email is already registered"]);
            exit;
        }
        $hashed     = password_hash($data['password'], PASSWORD_DEFAULT);
        $start_date = date('Y-m-d');
        $stmt = $pdo->prepare("INSERT INTO members 
            (fullname, email, password, phone, sex, birthdate, address, type, status, start_date, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())");
        $stmt->execute([
            $data['fullname'],
            $data['email'],
            $hashed,
            $data['phone'],
            $data['sex'],
            $data['birthdate'],
            $data['address'],
            $data['type'],
            $start_date,
        ]);
        $newMemberId = $pdo->lastInsertId();

        $totals      = ['Monthly' => 500, 'Quarterly' => 1300, 'Annual' => 4800];
        $total       = $totals[$data['type']] ?? 500;
        $paymentStmt = $pdo->prepare("INSERT INTO payments 
            (member, type, amount, total, created_at) 
            VALUES (?, ?, ?, ?, NOW())");
        $paymentStmt->execute([$newMemberId, $data['type'], $total, $total]);

        echo json_encode(["success" => true, "member_id" => $newMemberId]);

    // Admin add member flow - no email/password
    } else {
        $stmt = $pdo->prepare("INSERT INTO members 
            (fullname, address, phone, sex, type, birthdate, start_date) 
            VALUES (:fullname, :address, :phone, :sex, :type, :birthdate, :start_date)");
        $stmt->execute([
            ':fullname'   => $data['fullname'],
            ':address'    => $data['address'],
            ':phone'      => $data['phone'],
            ':sex'        => $data['sex'],
            ':type'       => $data['type'],
            ':birthdate'  => $data['birthdate'],
            ':start_date' => $data['start_date'],
        ]);
        echo json_encode(["success" => true]);
    }
}

// PUT - update member
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("UPDATE members SET 
        fullname=:fullname, address=:address, phone=:phone,
        sex=:sex, type=:type, birthdate=:birthdate, start_date=:start_date
        WHERE id=:id");
    $stmt->execute([
        ':id'         => $data['id'],
        ':fullname'   => $data['fullname'],
        ':address'    => $data['address'],
        ':phone'      => $data['phone'],
        ':sex'        => $data['sex'],
        ':type'       => $data['type'],
        ':birthdate'  => $data['birthdate'],
        ':start_date' => $data['start_date'],
    ]);
    echo json_encode(["success" => true]);
}

// DELETE - delete member
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $stmt = $pdo->prepare("DELETE FROM members WHERE id=:id");
    $stmt->execute([':id' => $data['id']]);
    echo json_encode(["success" => true]);
}

else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>