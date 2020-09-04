<?php

function isValidCommentIdx($idx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostComment WHERE commentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isUserLikedComment($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM CommentLike WHERE userIdx = ? and commentIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function modifyCommentLike($commentIdx, $userIdx, $likeIdx, $isLike)
{
    $pdo = pdoSqlConnect();

    if ($isLike == 'N') {
        $query = "update CommentLike set commentLikeIdx = ?, isDeleted = 'N' where commentIdx = ? and userIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$likeIdx, $commentIdx, $userIdx]);
    } else {
        $query = "update CommentLike set isDeleted = 'Y' where commentIdx = ? and userIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$commentIdx, $userIdx]);
    }

    $st = null;
    $pdo = null;
}

function makeCommentLike($commentIdx, $userIdx, $likeIdx)
{
    $pdo = pdoSqlConnect();

    $query = "insert into CommentLike (commentIdx,userIdx,commentLikeIdx) values (?,?,?)";
    $st = $pdo->prepare($query);
    $st->execute([$commentIdx, $userIdx, $likeIdx]);
    $st = null;
    $pdo = null;
}