<?php
$apiUrl   = 'http://localhost/GMS/api/members.php';
$response = file_get_contents($apiUrl);
$results  = json_decode($response, true);

foreach ($results as $row) {
    $age     = date_diff(date_create($row['birthdate']), date_create('now'))->y;
    $daysDue = (int) ((strtotime(date('Y-m-d', strtotime($row['start_date'] . ' +1 month'))) - strtotime(date('Y-m-d'))) / 86400);

    $class = '';
    $text  = '';
    if ($daysDue > 0) {
        $class = 'badge bg-success';
        $text  = 'Expire in ' . $daysDue . ' days';
    } elseif ($daysDue == 0) {
        $class = 'badge bg-warning';
        $text  = 'Expire Today';
    } else {
        $class = 'badge bg-danger';
        $text  = 'Expired';
    }

    if ($row['type'] == 'Regular') {
        $text_type = $row['type'] . ' - ₱300';
    } elseif ($row['type'] == 'Premium') {
        $text_type = $row['type'] . ' - ₱500';
    } else {
        $text_type = $row['type'] . ' - ₱800';
    }
?>
    <tr>
        <td>
            <img class="rounded-circle me-2" width="30" height="30" 
            src="https://bootdey.com/img/Content/avatar/avatar7.png">
            <?=$row['fullname']?>
        </td>
        <td><?=$row['address']?></td>
        <td><?=$row['phone']?></td>
        <td><?=$age?></td>
        <td><?=$row['sex']?></td>
        <td><?=$text_type?></td>
        <td><?=$row['start_date']?></td>
        <td><span class="<?=$class?>"><?=$text?></span></td>
        <td class="text-center">
            <button class="btn btn-primary mx-1" 
                role="button" 
                data-bs-target="#pay" 
                data-bs-toggle="modal" 
                data-id="<?=$row['id']?>" 
                data-type="<?=$row['type']?>">
                <i class="far fa-money-bill-alt"></i>&nbsp;Payment
            </button>
        </td>
    </tr>
<?php
}
?>