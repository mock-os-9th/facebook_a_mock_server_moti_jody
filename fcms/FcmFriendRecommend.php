<?php
require '/home/ubuntu/api-server/pdos/DatabasePdo.php';
//require '/home/ubuntu/api-server/pdos/FriendPdo.php';

$pdo = pdoSqlConnect();

$query = "select userIdx,bit_and(isDeleted='Y') as isNeedReset from FriendRecommend group by userIdx having isNeedReset = 1";
$st = $pdo->prepare($query);
$st->execute();

$st->setFetchMode(PDO::FETCH_ASSOC);
$reset = $st->fetchAll();

if(!is_null($reset)){
    foreach ($reset as $key => $item){
        $query = "update FriendRecommend set isDeleted = 'N' where userIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$item['userIdx']]);
    }
}

$query = "select UserToken.token,
       UserToken.userIdx,
       json_arrayagg(json_object('recommendUserIdx', FriendRecommend.recommendUserIdx, 'recommendUserName',
                                 concat(RecommendUser.firstName, RecommendUser.secondName))) as recommendUserIdxName
from (select userIdx, profileImgUrl, token from User where not isnull(token) and not token = '' and isDeleted = 'N') as UserToken
         left outer join FriendRecommend on FriendRecommend.userIdx = UserToken.userIdx
         left outer join User as RecommendUser on FriendRecommend.recommendUserIdx = RecommendUser.userIdx
where FriendRecommend.isDeleted = 'N'
group by token";

$st = $pdo->prepare($query);
$st->execute();

$st->setFetchMode(PDO::FETCH_ASSOC);
$res = $st->fetchAll();


foreach ($res as $key => $item){
    $recommendUser = json_decode($item['recommendUserIdxName'],true);
    $message = '새로운 친구 추천이 있습니다 : '.$recommendUser[0]['recommendUserName'].'님';
    $url='https://fcm.googleapis.com/fcm/send';
    $notification = array('body'=>$message,'title'=>'친구추천알림');
    $to = $item['token'];
    $fields = array('notification'=>$notification,'to'=>$to);

    $key = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";
    $headers = array('Authorization:key = '.$key,'Content-Type:application/json');

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

//    addFriendNotification(intval($item['userIdx']),intval($recommendUser[0]['recommendUserIdx']),$message);
//
//    $query = "update FriendRecommend set isDeleted = 'Y' where userIdx = ? and recommendUserIdx = ?";
//    $st = $pdo->prepare($query);
//    $st->execute([intval($item['userIdx']),intval($recommendUser[0]['recommendUserIdx'])]);
}