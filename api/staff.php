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

// GET - fetch all staff
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, username, fullname, address, phone, level, created_at FROM users");
    $staff = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($staff);
}

// POST - add staff
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }
    // Check for duplicate username or phone
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR phone = :phone");
    $stmt->execute([':username' => $data['username'], ':phone' => $data['phone']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["error" => "Staff already exists"]);
        exit;
    }
    $stmt = $pdo->prepare("INSERT INTO users 
        (fullname, phone, address, username, password, level) 
        VALUES (:fullname, :phone, :address, :username, :password, 1)");
    $stmt->execute([
        ':fullname' => $data['fullname'],
        ':phone'    => $data['phone'],
        ':address'  => $data['address'],
        ':username' => $data['username'],
        ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
    ]);
    echo json_encode(["success" => true]);
}

// PUT - update staff
elseif ($method === 'PUT') {
    $id   = $_GET['id'] ?? null;
    $data = json_decode(file_get_contents("php://input"), true);
    if (!$id || !$data) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }
    // Check for duplicate username or phone excluding current staff
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users 
        WHERE (username = :username OR phone = :phone) AND id != :id");
    $stmt->execute([
        ':username' => $data['username'],
        ':phone'    => $data['phone'],
        ':id'       => $id
    ]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode(["error" => "Staff already exists"]);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE users SET 
        username = :username,
        fullname = :fullname,
        address  = :address,
        phone    = :phone,
        password = :password
        WHERE id = :id");
    $stmt->execute([
        ':username' => $data['username'],
        ':fullname' => $data['fullname'],
        ':address'  => $data['address'],
        ':phone'    => $data['phone'],
        ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
        ':id'       => $id
    ]);
    echo json_encode(["success" => true]);
}

// DELETE - delete staff
elseif ($method === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id   = $data['id'] ?? ($_GET['id'] ?? null);
    if (!$id) {
        echo json_encode(["error" => "ID required"]);
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(["success" => true]);
}

else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>