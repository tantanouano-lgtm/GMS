<?php
$apiUrl   = 'http://localhost/GMS/api/dashboard.php';
$response = file_get_contents($apiUrl);
$data     = json_decode($response, true);

function yearly_chart() {
    global $data;
    $chartData = [
        'labels' => $data['yearlyChart']['labels'],
        'datasets' => [[
            'label'           => 'Yearly Earnings',
            'fill'            => true,
            'data'            => $data['yearlyChart']['data'],
            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
            'borderColor'     => 'rgba(78, 115, 223, 1)'
        ]]
    ];
    $chartDataJson = json_encode($chartData);
?>
    <canvas data-bss-chart='{"type":"line","data":<?php echo $chartDataJson; ?>,"options":{"maintainAspectRatio":false,"legend":{"display":false,"labels":{"fontStyle":"normal"}},"title":{"fontStyle":"normal"},"scales":{"xAxes":[{"gridLines":{"color":"rgb(234, 236, 244)","zeroLineColor":"rgb(234, 236, 244)","drawBorder":false,"drawTicks":false,"borderDash":["2"],"zeroLineBorderDash":["2"],"drawOnChartArea":false},"ticks":{"fontColor":"#858796","fontStyle":"normal","padding":20}}],"yAxes":[{"gridLines":{"color":"rgb(234, 236, 244)","zeroLineColor":"rgb(234, 236, 244)","drawBorder":false,"drawTicks":false,"borderDash":["2"],"zeroLineBorderDash":["2"]},"ticks":{"fontColor":"#858796","fontStyle":"normal","padding":20}}]}}}'></canvas>
<?php
}

function month_chart() {
    global $data;
    $chartData = [
        'labels' => $data['monthlyChart']['labels'],
        'datasets' => [[
            'label'           => 'Earnings',
            'fill'            => true,
            'data'            => $data['monthlyChart']['data'],
            'backgroundColor' => 'rgba(78, 115, 223, 0.05)',
            'borderColor'     => 'rgba(78, 115, 223, 1)'
        ]]
    ];
    $chartDataJson = json_encode($chartData);
?>
    <canvas data-bss-chart='{"type":"line","data":<?php echo $chartDataJson; ?>,"options":{"maintainAspectRatio":false,"legend":{"display":false,"labels":{"fontStyle":"normal"}},"title":{"fontStyle":"normal"},"scales":{"xAxes":[{"gridLines":{"color":"rgb(234, 236, 244)","zeroLineColor":"rgb(234, 236, 244)","drawBorder":false,"drawTicks":false,"borderDash":["2"],"zeroLineBorderDash":["2"],"drawOnChartArea":false},"ticks":{"fontColor":"#858796","fontStyle":"normal","padding":20}}],"yAxes":[{"gridLines":{"color":"rgb(234, 236, 244)","zeroLineColor":"rgb(234, 236, 244)","drawBorder":false,"drawTicks":false,"borderDash":["2"],"zeroLineBorderDash":["2"]},"ticks":{"fontColor":"#858796","fontStyle":"normal","padding":20}}]}}}'></canvas>
<?php
}

function get_gender_piechart() {
    global $data;
    $chartData = [
        'type' => 'doughnut',
        'data' => [
            'labels' => ['Male', 'Female', 'Referral'],
            'datasets' => [[
                'label'           => '',
                'backgroundColor' => ['#4e73df', '#1cc88a'],
                'borderColor'     => ['#ffffff', '#ffffff'],
                'data'            => [
                    $data['gender']['male_count'],
                    $data['gender']['female_count'],
                    $data['gender']['referral_count']
                ]
            ]]
        ],
        'options' => [
            'maintainAspectRatio' => false,
            'legend'              => ['display' => false, 'labels' => ['fontStyle' => 'normal']],
            'title'               => ['fontStyle' => 'normal']
        ]
    ];
    $chartDataJson = json_encode($chartData);
?>
    <canvas data-bss-chart='<?php echo htmlspecialchars($chartDataJson); ?>'></canvas>
<?php
}
?>