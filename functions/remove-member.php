<?php
include_once 'connection.php';

$id = $_POST['id'];

// Delete member via API (DELETE)
$apiUrl = 'http://localhost/GMS/api/members.php';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['id' => $id]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = json_decode(curl_exec($ch), true);
curl_close($ch);

if (!isset($response['success'])) {
    header('Location: ../members.php?type=error&message=Failed to remove member');
    exit;
}

generate_logs('Remove member', $id . '| Member details were removed');
header('Location: ../members.php?type=success&message=Member details were removed successfully');
exit;
?>