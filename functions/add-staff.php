<?php
include_once 'connection.php';

$fullname = $_POST['fullname'];
$phone    = $_POST['phone'];
$address  = $_POST['address'];
$username = $_POST['username'];
$password = $_POST['password'];

// Check if staff already exists via API
$apiUrl   = 'http://localhost/GMS/api/staff.php';
$existing = json_decode(file_get_contents($apiUrl), true);

foreach ($existing as $staff) {
    if ($staff['username'] == $username || $staff['phone'] == $phone) {
        header('Location: ../staff.php?type=error&message=Staff already exists');
        exit;
    }
}

// Add staff via API (POST)
$data = [
    'fullname' => $fullname,
    'phone'    => $phone,
    'address'  => $address,
    'username' => $username,
    'password' => $password,
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['success'])) {
    header('Location: ../staff.php?type=error&message=Failed to add staff');
    exit;
}

generate_logs('Add Staff', 'New staff was added: ' . $username);
header('Location: ../staff.php?type=success&message=Staff details were added successfully');
?>