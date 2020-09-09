<?php
function setFcmTokenToUser($token,$userIdx)
{
    $pdo = pdoSqlConnect();

    $query = "update User set token = '' where token = ?";

    $st = $pdo->prepare($query);
    $st->execute([$token]);

    $query = "update User set token = ? where userIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$token, $userIdx]);
}