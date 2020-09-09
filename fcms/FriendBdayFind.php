<?php
require '/var/www/html/api/pdos/DatabasePdo.php';

$pdo = pdoSqlConnect();

$query = "select userIdx, concat(firstName, ' ', secondName) as userName
        from User
        where DATE_FORMAT(bday,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d') and isDeleted = 'N';";

$st = $pdo->prepare($query);
$st->execute();
$st->setFetchMode(PDO::FETCH_ASSOC);
$res = $st->fetchAll();

//$st = null; $pdo = null;
if(sizeof($res) > 0){
    foreach($res as $users) {
        foreach($users as $user) {
            $bdayUserIdx = intval($user['userIdx']);
            $bdayUserName = strval($user['userName']);

            $query = "select token
            from Friends as f
                inner join (select token, userIdx from User) as u on u.userIdx = f.userIdx
            where friendIdx = $bdayUserIdx and isDeleted = 'N';";

            $st = $pdo->prepare($query);
            $st->execute();
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $res = $st->fetchAll();

            $alertTitle = "생일 알림";
            $alertContent = "오늘은 ". $bdayUserName ."님의 생일 입니다. 좋은 일이 가득하길 바라는 마음을 전해보세요!";
            $link = "http://54.180.68.232/user/$bdayUserIdx/profile/info";

            $message = array(
                "title"     => $alertTitle,
                "body"   => $alertContent,
                "link"      => $link
            );

            if(sizeof($res) > 0 ){
                foreach($res as $tokens) {
                    foreach($tokens as $token) {
                        $notiUserIdx = getUserIdxByToken(strval($token));
                        if($notiUserIdx != $bdayUserIdx) {
                            send_friend_bday_notification(strval($token), $message);
                            addUserNotification($bdayUserIdx, $notiUserIdx, $alertTitle, $link, 'B');
                        }
                    }
                }
            }
        }
    }
}

//$st = null; $pdo = null;

//생일 자 여러명일 때
//리스트정리
//한명이상 생일 일때?



function send_friend_bday_notification($token, $message)
{
    $GOOGLE_API_KEY = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";
    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
        'to' => $token,
        'notification'             => $message
    );

    $headers = array(
        'Authorization:key =' . $GOOGLE_API_KEY,
        'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        die('Curl failed: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function getUserIdxByToken($token)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM User WHERE token = ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$token]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["userIdx"]);
}
function addUserNotification($senderIdx, $receiverIdx, $alertTitle, $link)
{
    $pdo = pdoSqlConnect();

    $query = "insert into UserNotification (senderIdx, receiverIdx, notificationContent, link, notificationType) values (?, ?, ?, ?, 'P')";

    $st = $pdo->prepare($query);
    $st->execute([$senderIdx, $receiverIdx, $alertTitle, $link]);

    $st = null;
    $pdo = null;
}