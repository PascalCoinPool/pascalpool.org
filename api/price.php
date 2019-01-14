<?php

$cache_file = __DIR__.'/cache/price.json';

if (time() - filemtime($cache_file) < 60 * 4) {
    $price = file_get_contents($cache_file);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo $price;
    exit;
}

$currencies = array('BTC','USD','CAD','AUD','EUR','GBP','JPY','RUR','UAH','IDR','BRL');

// Get Live Price
$currencies = implode(',', $currencies);
$api_link = 'https://min-api.cryptocompare.com/data/price?fsym=PASC&tsyms='.$currencies.'&extraParams=pascalcoin_pool_page';
$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_URL => $api_link,
));
$price = curl_exec($curl);
curl_close($curl);
$data = json_decode($price, true);

if(!isset($data['Response']) || $data['Response'] != 'Error') {
    file_put_contents($cache_file, $price);
} else {
    $price = file_get_contents($cache_file);
}

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
echo $price;
exit;
