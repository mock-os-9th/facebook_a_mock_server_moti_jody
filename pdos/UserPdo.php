<?php

function getUserCareer($idx, $bound)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT careerName FROM UserCareer WHERE userIdx = ? AND careerPrivacyBounds = ? AND isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $bound]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isCareerIdxExists($idx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserCareer WHERE userIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidUserIdx($idx)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM User WHERE userIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidAccessRights($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "SELECT NOT EXISTS(SELECT * FROM Blocked WHERE userIdx = ? AND blockedUserIdx = ? AND isDeleted = 'N') AS notExist";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["notExist"]);
}

function getUserIdxFromId($id)
{
    $pdo = pdoSqlConnect();

    if (isValidPhoneNum($id)) {
        $query = "select userIdx from User where phoneNum = ? and isDeleted = 'N'";
    } else if (isValidEmail($id)) {
        $query = "select userIdx from User where email = ? and isDeleted = 'N'";
    } else {
        return false;
    }

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["userIdx"]);
}