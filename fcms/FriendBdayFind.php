<?php
require '/home/ubuntu/api-server/pdos/DatabasePdo.php';

$pdo = pdoSqlConnect();

//$query = "select userIdx, concat(firstName, ' ', secondName) as userName
//        from User
//        where DATE_FORMAT(bday,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d');";
//
//$st = $pdo->prepare($query);
//$st->execute();
//$st->setFetchMode(PDO::FETCH_ASSOC);
//$res = $st->fetchAll();
//
//$st = null; $pdo = null;
//
//$bdayUserIdx = intval($res[0]['userIdx']);
//$bdayUserName = strval($res[0]['userName']);

$query = "select token
        from Friends as f
            inner join (select token, userIdx from User) as u on u.userIdx = f.userIdx
        where friendIdx = 50 and isDeleted = 'N';";

$st = $pdo->prepare($query);
$st->execute();
$st->setFetchMode(PDO::FETCH_ASSOC);
$res = $st->fetchAll();

$st = null; $pdo = null;

//생일 자 여러명일 때
//리스트정리
//한명이상 생일 일때?

$alertTitle = "생일 알림 입니다";
$alertContent = "오늘은 이민아님의 생일 입니다. 좋은 일이 가득하길 바라는 마음을 전해보세요!";
$link = "http://54.180.68.232/user/50/profile/info";

    $message = array(
        "title"     => $alertTitle,
        "body"   => $alertContent,
        "link"      => $link
    );

    if(sizeof($res) > 0 ){
        foreach($res as $tokens) {
            foreach($tokens as $token) {
                $notiUserIdx = getUserIdxByToken(strval($token));
                if($notiUserIdx != 50) {
                    send_friend_bday_notification(strval($token), $message);
                    addUserNotification(50, $notiUserIdx, $alertTitle, $link, 'P');
                }
            }
        }
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