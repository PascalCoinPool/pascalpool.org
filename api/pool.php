<?php

include('common.php');

$pools = json_decode(file_get_contents('../pascalcoin-pools-json/pascalcoin-pools.json'), JSON_OBJECT_AS_ARRAY);

$data = json_decode(urldecode($_GET['data']), JSON_OBJECT_AS_ARRAY);

$pool = null;

foreach($pools['pools'] as $_pool) {
    if($_pool['api'] == $data['api'] && $_pool['type'] == $data['type']) {
        $pool = $_pool;
        break;
    }
}

if($pool == null) {
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo json_encode(array('status'=>'error'));
    exit;
}

$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $pool['name'])));
$cache_file = __DIR__.'/cache/pool-'.$slug.'.json';

if(time() - filemtime($cache_file) < 60 * 1) {
    $pool_data = file_get_contents($cache_file);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo $pool_data;
    exit;
}

$pool_data = $pool + array(
    'fee' => 'N/A',
    'min_payment' => 'N/A',
    'hashrate' => 'N/A',
    'blocks_found' => 'N/A',
    'last_block' => 'N/A',
    'miners' => 'N/A',
    'miners_paid' => 'N/A',
    'payments' => 'N/A',
    'height' => 'N/A'
);

if($pool['type'] == 'openpool') {
    $url = $pool['api'].'stats';

    $response = callAPI($url);

    if($response) {

        $fee = 0;
        $fee += $response['config']['fee'];
        if($response['config']['donation'] ?? null) {
            foreach($response['config']['donation'] as $donation) {
                $fee += $donation;
            }
        }

        $pool_data['fee'] = $fee;
        $pool_data['min_payment'] = $response['config']['minPaymentThreshold'];
        $pool_data['hashrate'] = $response['pool']['hashrate'];
        $pool_data['blocks_found'] = $response['pool']['totalBlocks'];
        $pool_data['last_block'] = $response['pool']['lastBlockFound'] ?? 0;
        $pool_data['miners'] = $response['pool']['miners'];
        $pool_data['miners_paid'] = $response['pool']['totalMinersPaid'];
        $pool_data['payments'] = $response['pool']['totalPayments'];
        $pool_data['height'] = $response['network']['height'];

    }

    $pool_data['status'] = 'ok';

} else if($pool['type'] == 'nanopool') {
    $url = $pool['api'];

    $activeminers = callAPI($url.'pool/activeminers');
    //$activeworkers = callAPI($url.'pool/activeworkers');
    $hashrate = callAPI($url.'pool/hashrate');
    $blocks = callAPI($url.'pool/count_blocks/100000');
    $last_block = callAPI($url.'/pool/blocks/0/1');
    $height = callAPI($url.'/network/lastblocknumber');
    
    $pool_data['fee'] = 2;
    $pool_data['min_payment'] = 1;

    if($hashrate['status'])
        $pool_data['hashrate'] = $hashrate['data'];

    if($blocks['status'])
        $pool_data['blocks_found'] = $blocks['data']['count'];

    if($last_block['status'])
        $pool_data['last_block'] = $last_block['data'][0]['date'] * 1000;

    if($activeminers['status'])
        $pool_data['miners'] = $activeminers['data'];

    if($height['status'])
        $pool_data['height'] = $height['data'];

    $pool_data['status'] = 'ok';

} else if($pool['type'] == 'coinotron') {
    $url = $pool['api'].'&method=poolStats';
    $response = callAPI($url);

    if($response['status'] == 'OK') {
        for($i = 0; $i < count($response['data']); $i++) {
            if($response['data'][$i]['info']['pool'] == 'PASC') {
                $pool_info = $response['data'][$i];
                
                $pool_data['fee'] = 2;
                $pool_data['min_payment'] = 10;
                $pool_data['hashrate'] = $pool_info['poolStats']['hashrate'];
                $pool_data['miners'] = $pool_info['poolStats']['miners'];

                $pool_data['status'] = 'ok';
                
            }
        }
    }
    
} else if($pool['type'] == 'f2pool') {

    $url = $pool['api'];
    //$response = callAPI($url);

    $pool_data['fee'] = 2;

    //$pool_data['hashrate'] = $response['hashrate_history']
    
    $pool_data['status'] = 'ok';
    
} else {
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo json_encode(array('status'=>'error'));
    exit;
}

file_put_contents($cache_file, json_encode($pool_data));

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
echo json_encode($pool_data, JSON_PRETTY_PRINT);
