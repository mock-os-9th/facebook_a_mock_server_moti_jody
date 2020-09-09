<?php
require '/home/ubuntu/api-server/pdos/DatabasePdo.php';

$pdo = pdoSqlConnect();
$query = "select token, json_arrayagg(recommendUserIdx) as recommendUserIdx
from (select userIdx, token from User where not isnull(token) and isDeleted = 'N') as UserToken
         left outer join FriendRecommend on FriendRecommend.userIdx = UserToken.userIdx
where FriendRecommend.isDeleted = 'N'
group by token";

$st = $pdo->prepare($query);
$st->execute();

$st->setFetchMode(PDO::FETCH_ASSOC);
$res = $st->fetchAll();


foreach ($res as $key => $item){
//    $url='https://fcm.googleapis.com/fcm/send';
//    $notification = array('body'=>intval($item['recommendUserIdx'][0]),'title'=>'친구추천알림');
//    $to = $item['token'];
//    $fields = array('notification'=>$notification,'to'=>$to);
//
//    $key = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";
//    $headers = array('Authorization:key = '.$key,'Content-Type:application/json');
//
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, $url);
//    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
//
//    $result = curl_exec($ch);
//    if($result === false){
//        die('Curl failed:'.curl_error($ch));
//    }
//    curl_close($ch);
    echo $item.recommendUserIdx[0];
}