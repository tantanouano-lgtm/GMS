<?php
include_once 'connection.php';

$id         = $_POST['id'];
$fullname   = $_POST['fullname'];
$sex        = $_POST['sex'];
$phone      = $_POST['phone'];
$address    = $_POST['address'];
$type       = $_POST['type'];
$start_date = $_POST['start_date'];
$birthdate  = $_POST['birthdate'];

// Step 1 - Check if member already exists via API
$apiUrl = 'http://localhost/GMS/api/members.php';
$existing = json_decode(file_get_contents($apiUrl), true);

foreach ($existing as $member) {
    if ($member['id'] != $id) {
        if ($member['fullname'] == $fullname || $member['phone'] == $phone) {
            header('Location: ../members.php?type=error&message=Member already exists');
            exit;
        }
    }
}

// Step 2 - Update member via API (PUT)
$data = [
    'id'         => $id,
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
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['success'])) {
    header('Location: ../members.php?type=error&message=Failed to update member');
    exit;
}

generate_logs('Update', $id . '| Member was updated');
header('Location: ../members.php?type=success&message=Member was updated successfully');
?>