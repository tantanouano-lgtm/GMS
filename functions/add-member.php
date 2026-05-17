<?php
include_once 'connection.php';

$fullname   = $_POST['fullname'];
$sex        = $_POST['sex'];
$phone      = $_POST['phone'];
$address    = $_POST['address'];
$type       = $_POST['type'];
$start_date = $_POST['start_date'];
$birthdate  = $_POST['birthdate'];
$amount     = $_POST['amount'];

// Step 1 - Check if member already exists via API
$apiUrl = 'http://localhost/GMS/api/members.php';
$existing = json_decode(file_get_contents($apiUrl), true);

foreach ($existing as $member) {
    if ($member['fullname'] == $fullname || $member['phone'] == $phone) {
        header('Location: ../members.php?type=error&message=Member already exists');
        exit;
    }
}

// Step 2 - Calculate total based on type
if ($type == 'Regular') {
    $total = 300;
} elseif ($type == 'Premium') {
    $total = 500;
} else {
    $total = 800;
}

$change = $amount - $total;
if ($change < 0) {
    header('Location: ../members.php?type=error&message=Amount is not enough');
    exit;
}

// Step 3 - Add member via API (POST)
$data = [
    'fullname'   => $fullname,
    'sex'        => $sex,
    'phone'      => $phone,
    'address'    => $address,
    'type'       => $type,
    'start_date' => $start_date,
    'birthdate'  => $birthdate,
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!$response['success']) {
    header('Location: ../members.php?type=error&message=Failed to add member');
    exit;
}

// Step 4 - Get the new member ID (fetch all, get last one)
$allMembers = json_decode(file_get_contents($apiUrl), true);
$newMember = end($allMembers);
$id = $newMember['id'];

// Step 5 - Add payment directly (payments not in API yet, keep DB for now)
$sql = "INSERT INTO payments (member, type, amount, total) 
        VALUES (:member, :type, :amount, :total)";
$stmt = $db->prepare($sql);
$stmt->bindParam(':member', $id);
$stmt->bindParam(':type', $type);
$stmt->bindParam(':amount', $amount);
$stmt->bindParam(':total', $total);
$stmt->execute();
$paymentId = $db->lastInsertId();

// Step 6 - Log and redirect
generate_logs('Payment', $id . '| Payment was made');
header('Location: ../reciept.php?id=' . $paymentId);
?>