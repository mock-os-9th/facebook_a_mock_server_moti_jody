<?php
$id = $_POST['id'];
$pw = $_POST['pw'];
$token = $_POST['token'];

$url='http://15.164.195.62/login';
$fields = array('id',$id,'pw',$pw);

$headers = array('Content-Type:application/json');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

$result = curl_exec($ch);
if($result === false){
    die('Curl failed:'.curl_error($ch));
}
curl_close($ch);

$jwt = json_decode($result);
$jwt = $jwt->result->jwt;
echo $jwt;


