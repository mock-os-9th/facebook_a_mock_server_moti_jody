<?php
function setFcmTokenToUser($token,$userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "update User set token = null where token = ?";

    $st = $pdo->prepare($query);
    $st->execute([$token]);

    $query = "update User set token = ? where userIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$token, $userIdx]);
}

function getUserFcmToken($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select token from User where userIdx = ?";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res[0]["token"];
}

function getRecommendFriends($userIdx){
    $pdo = pdoSqlConnect();
    $query = "select recommendUserIdx from FriendRecommend where userIdx = ? and isDeleted = 'N' limit 0,2";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    return $res;
}