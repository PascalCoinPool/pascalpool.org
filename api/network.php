<?php

$cache_file = __DIR__.'/cache/network.json';

if(time() - filemtime($cache_file) < 60 * 1) {
    $network = file_get_contents($cache_file);
    header("Access-Control-Allow-Origin: *");
    header('Content-Type: application/json');
    echo $network;
    exit;
}

include('common.php');

$response = callRPC('getblocks', ['last'=>1]);

if($response) {
    $response = $response["result"][0];
    file_put_contents($cache_file, json_encode($response));
} else {
    $response = json_decode(file_get_contents($cache_file));
}


header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
echo json_encode($response, JSON_PRETTY_PRINT);

