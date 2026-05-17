<?php
include_once 'connection.php';

$id       = $_POST['id'];
$fullname = $_POST['fullname'];
$phone    = $_POST['phone'];
$address  = $_POST['address'];
$username = $_POST['username'];
$password = $_POST['password'];

// Step 1 - Check if username or phone already taken by another staff via API
$apiUrl   = 'http://localhost/GMS/api/staff.php';
$existing = json_decode(file_get_contents($apiUrl), true);

foreach ($existing as $staff) {
    if ($staff['id'] != $id) {
        if ($staff['username'] == $username || $staff['phone'] == $phone) {
            header('Location: ../staff.php?type=error&message=Staff already exists');
            exit;
        }
    }
}

// Step 2 - Update staff via API (PUT)
$data = [
    'fullname' => $fullname,
    'phone'    => $phone,
    'address'  => $address,
    'username' => $username,
    'password' => $password,
];

$ch = curl_init($apiUrl . '?id=' . $id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['success'])) {
    header('Location: ../staff.php?type=error&message=Failed to update staff');
    exit;
}

generate_logs('Update Staff', $id . '| Staff was updated: ' . $username);
header('Location: ../staff.php?type=success&message=Staff details were updated successfully');
?>