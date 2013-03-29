<?php

// Basic Settings
include('Crypt/RSA.php');
$toURL = "http://localhost/clone/SecureHTTP/server.php";
$debug = true;

// Create Client Key Pair
$rsa = new Crypt_RSA();
extract($rsa->createKey());

$data = array(
  "REQUEST" => "connect",
  "CONTENT" => $publickey,
);

// Establish a connection with server
$connection = curl_init();
$result = send_request($connection, $data, $toURL);

// Perform a JSON Decode
$json_result = json_decode($result);

// Decrypt server respond and get server public key
$rsa->loadKey($privatekey);
$server_public_key = $rsa->decrypt(base64_decode($json_result->PUBLIC_KEY));

if($debug)
{
    echo "[Server Respond]\n";
    echo $result."\n";
    echo "[Decrypt]\n";
    echo $server_public_key."\n";
}


// Sending Message to server
$msg = '$a=1;$b=2;$respond = $a+$b;';

// Encrypt message with server public key
$rsa->loadKey($server_public_key);
$chipper_msg = $rsa->encrypt($msg);

if($debug)
{
    echo "[Client Chipper]\n";
    echo base64_encode($chipper_msg)."\n";
}

// Sending Request to Server
$data = array(
  "REQUEST" => "transmit",
  "CONTENT" => base64_encode($chipper_msg),
  "CLIENT_ID" => $json_result->CLIENT_ID, 
);
$result = send_request($connection, $data, $toURL);
$result_obj = json_decode($result);

$rsa->loadKey($privatekey);
$server_respond = $rsa->decrypt(base64_decode($result_obj->CONTENT));

if($debug)
{
    echo "[Server Respond]\n";    
    echo $result."\n";
    echo "[Decrypt]\n";
    echo $server_respond."\n";
}

$data = array(
  "REQUEST" => "disconnect",
);
$result = send_request($connection, $data, $toURL);

curl_close($connection);

function send_request( $handle, $arr, $url)
{
    $options = array(
        CURLOPT_URL=>$url,
        CURLOPT_HEADER=>0,
        CURLOPT_VERBOSE=>0,
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_USERAGENT=>"Mozilla/4.0 (compatible;)",
        CURLOPT_POST=>true,
        CURLOPT_POSTFIELDS=>http_build_query($arr),
    );
    curl_setopt_array($handle, $options);
    return curl_exec( $handle ); 
}

?>