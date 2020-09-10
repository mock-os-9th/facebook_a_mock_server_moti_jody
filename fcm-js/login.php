<?php
$id = $_POST['id'];
$pw = $_POST['pw'];
$token = $_POST['token'];

$url='https://myphotoexhibition.site/login';
$fields = array("id"=>$id,"pw"=>$pw);

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

$jwt = json_decode($result,true);
$jwt = $jwt['result']['jwt'];

$url='https://myphotoexhibition.site/fcm';
$fields = array("token"=>$token);

$headers = array('Content-Type:application/json','x-access-token:'.$jwt);

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

header('Location:https://myphotoexhibition.site/fcm-js/notification.html?data='.$jwt);