<?php
include_once 'connection.php';
session_start();

if (!isset($_SESSION['member_id'])) {
    header('Location: ../index.php');
    exit;
}

$id   = $_SESSION['member_id'];
$type = $_POST['type'] ?? '';

$plans = [
    'Regular' => 300,
    'Premium' => 500,
    'VIP'     => 800,
];

if (!array_key_exists($type, $plans)) {
    header('Location: ../member_dashboard.php?page=payment&type=error&message=Invalid plan selected');
    exit;
}

// Submit payment via API
$apiUrl = 'http://localhost/GMS/api/payments.php';
$data   = [
    'member' => $id,
    'type'   => $type,
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['success'])) {
    header('Location: ../member_dashboard.php?page=payment&type=error&message=Payment failed, please try again');
    exit;
}

generate_logs('Payment', $id . '| Payment was made for plan: ' . $type);
header('Location: ../member_dashboard.php?page=payment&type=success&message=Payment submitted successfully!');
exit;
?>