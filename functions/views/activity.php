<?php
$apiUrl   = 'http://localhost/GMS/api/logs.php';
$response = file_get_contents($apiUrl);
$results  = json_decode($response, true);

foreach ($results as $row) {
?>
    <tr>
        <td><?=$row['id']?></td>
        <td>
            <img class="rounded-circle me-2" width="30" height="30" 
            src="https://bootdey.com/img/Content/avatar/avatar7.png">
            <?=$row['username'] ?? 'Unknown'?>
        </td>
        <td><?=$row['type']?></td>
        <td><?=$row['logs']?></td>
        <td><?=$row['created_at']?></td>
    </tr>
<?php
}
?>