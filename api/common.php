<?php

function callAPI($url) {

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

    if($response) {
        return $response;
    }

    $response = json_decode(file_get_contents($url), JSON_OBJECT_AS_ARRAY);

    return $response;

}

function callRPC($method, $params=[], $host="127.0.0.1", $port=4003) {
    $id = 1;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://{$host}:{$port}/json_rpc");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
    if(empty($params))
        $request = json_encode(['jsonrpc'=>'2.0','method'=>$method,'id'=>$id]);
    else
        $request = json_encode(['jsonrpc'=>'2.0','method'=>$method,'params'=>$params,'id'=>$id]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    $response = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($response, JSON_OBJECT_AS_ARRAY);

    return $response;

}
