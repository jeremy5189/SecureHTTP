<?php
include('Crypt/RSA.php');
include('config.php');

$link = mysql_connect(DB_HOST, DB_USER, DB_PASS);
mysql_select_db(DB_NAME); 
mysql_query("SET NAMES UTF8;");

$request = $_POST['REQUEST'];
$content = $_POST['CONTENT'];
$clientid = $_POST['CLIENT_ID'];
$active = 1;

// Starting a new Secure HTTP Connection
if( $request == 'connect' )
{
    // Receiving Client Public Key
    $client_public_key = $content;
    
    // Generating Server Key Pair
    $rsa = new Crypt_RSA();
    extract($rsa->createKey());
    
    // Generating Client ID
    $client_id = substr( md5( uniqid() ), 0 , 8);
    
    // Saving Keys in MySQL
    $sql = "INSERT INTO $TABLE_NAME (`CLIENT_ID` ,
                                    `CLIENT_PUBLIC_KEY` ,
                                    `SERVER_PRIVATE_KEY`,
                                    `SERVER_PUBLIC_KEY`,
                                    `ACTIVE`) 
                             VALUES ('$client_id',  
                                     '$client_public_key',  
                                     '$privatekey', 
                                     '$publickey',
                                      $active );";
    mysql_query($sql, $link);
    
    // Encrypt Server Public Key With Client Public Key
    $rsa->loadKey($client_public_key);
    $ciphertext = $rsa->encrypt($publickey);
    
    // Responding Encrypted Server Public Key and Client ID in JSON
    $res = array( 'CLIENT_ID' => $client_id, 'PUBLIC_KEY' => base64_encode($ciphertext) );
    echo json_encode($res);
}
else if( $request == 'transmit' )
{
    // Receiving Encrypted Client Transmition
    $encrypted_client_data = base64_decode($content);
    
    // Retrieve Key from MySQL
    $sql = "SELECT * FROM $TABLE_NAME WHERE `CLIENT_ID` = '$clientid';";
    $result = mysql_query($sql, $link);
    $data = mysql_fetch_object($result);
    
    // Decrypting Contents
    $rsa = new Crypt_RSA();
    $rsa->loadKey($data->SERVER_PRIVATE_KEY);
    $plain_client_data = $rsa->decrypt($encrypted_client_data);
    
    // Some Codes here
    //eval($plain_client_data);
    $respond = $plain_client_data;
    
    // Encrypt Server Public Key With Client Public Key
    $rsa->loadKey($data->CLIENT_PUBLIC_KEY);
    $ciphertext = $rsa->encrypt($respond);
    
    // Responding Encrypted Server Public Key
    $res = array( 'CLIENT_ID' => $clientid, 'CONTENT' => base64_encode($ciphertext) );
    echo json_encode($res);
  
}
else if( $request == 'disconnect' )
{
    $sql = "DELETE FROM $TABLE_NAME WHERE `CLIENT_ID` = '$clientid';";
    mysql_query($sql, $link);
}

mysql_close($link);

?>