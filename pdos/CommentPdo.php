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

function getComment($userIdx, $postIdx, $page, $limit)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select pc.commentIdx, u.*, commentContents, commentImgUrl,
                       pcc.replyCount,
                       case
                           when (timestampdiff(month, createAt, now()) > 6)
                               then concat(timestampdiff(year, createAt, now()), '년')
                           when (timestampdiff(day, createAt, now()) > 30)
                               then concat(timestampdiff(month, createAt, now()),'달')
                           when (timestampdiff(hour, createAt, now()) > 24 )
                               then concat(timestampdiff(day, createAt, now()),'일')
                           when (timestampdiff(minute , createAt, now()) > 60)
                               then concat(timestampdiff(hour, createAt, now()),'시간')
                           when (timestampdiff(second, createAt, now()) > 60)
                               then concat(timestampdiff(minute , createAt, now()),'분')
                           else concat(timestampdiff(second, createAt, now()),'초')
                       end as commentDate,
                       cl.likeCount,
                       (select exists(select * from CommentLike
                        where userIdx = $userIdx and commentIdx = pc.commentIdx)) as isLiked,
                       (select exists(select * from UserCommentHide
                        where userIdx = $userIdx and commentIdx = pc.commentIdx)) as isHided
                from PostComment as pc
                    inner join (select userIdx, concat(firstName, ' ', secondName) as userName, profileImgUrl from User
                        where userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                        or userIdx not in (select userIdx from Blocked where blockedUserIdx = $userIdx and Blocked.isDeleted = 'N')
                        ) as u on pc.userIdx = u.userIdx
                    left join (select parentCommentIdx, count(*) as replyCount from PostComment
                        where parentCommentIdx is not null
                        and userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                        or userIdx not in (select userIdx from Blocked where blockedUserIdx = $userIdx and Blocked.isDeleted = 'N')
                        group by parentCommentIdx) as pcc on pcc.parentCommentIdx = pc.commentIdx
                    left join (select commentIdx, count(*) as likeCount from CommentLike
                        where userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                        or userIdx not in (select userIdx from Blocked where blockedUserIdx = $userIdx and Blocked.isDeleted = 'N')
                        group by commentIdx) as cl on cl.commentIdx = pc.commentIdx
                where pc.postIdx = $postIdx and pc.parentCommentIdx is null 
                order by createAt desc
                limit $page, $limit;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isPostExist($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Posts WHERE postIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isPostCommentExist($postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostComment WHERE postIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getCommentReply($userIdx, $commentIdx, $page, $limit)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select pc.commentIdx as replyIdx, u.*, commentContents, commentImgUrl,
                   case
                       when (timestampdiff(month, createAt, now()) > 6)
                           then concat(timestampdiff(year, createAt, now()), '년')
                       when (timestampdiff(day, createAt, now()) > 30)
                           then concat(timestampdiff(month, createAt, now()),'달')
                       when (timestampdiff(hour, createAt, now()) > 24 )
                           then concat(timestampdiff(day, createAt, now()),'일')
                       when (timestampdiff(minute , createAt, now()) > 60)
                           then concat(timestampdiff(hour, createAt, now()),'시간')
                       when (timestampdiff(second, createAt, now()) > 60)
                           then concat(timestampdiff(minute , createAt, now()),'분')
                       else concat(timestampdiff(second, createAt, now()),'초')
                   end as replyDate,
                   cl.likeCount,
                   (select exists(select * from CommentLike
                    where userIdx = $userIdx and commentIdx = pc.commentIdx and isDeleted = 'N')) as isLiked,
                   (select exists(select * from UserCommentHide
                    where userIdx = $userIdx and commentIdx = pc.commentIdx and isDeleted = 'N')) as isHided
            from PostComment as pc
                inner join (select userIdx, concat(firstName, ' ', secondName) as userName, profileImgUrl from User
                    where (userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                    or userIdx not in (select userIdx from Blocked where blockedUserIdx = $userIdx and Blocked.isDeleted = 'N'))
                    and isDeleted = 'N'
                    ) as u on pc.userIdx = u.userIdx
                left join (select commentIdx, count(*) as likeCount from CommentLike
                    where (userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                    or userIdx not in (select userIdx from Blocked where blockedUserIdx = $userIdx and Blocked.isDeleted = 'N'))
                    and isDeleted = 'N'
                    group by commentIdx) as cl on cl.commentIdx = pc.commentIdx
            where pc.parentCommentIdx = $commentIdx and pc.parentCommentIdx is not null and pc.isDeleted = 'N'
            order by createAt desc
            limit $page, $limit;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}
function isCommentExist($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostComment WHERE commentIdx = ? and parentCommentIdx is null and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isCommentReplyExistOnComment($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostComment WHERE parentCommentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function createComment($userIdx, $postIdx, $commentContent, $commentImgUrl)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO PostComment (userIdx, postIdx, commentContents, commentImgUrl) VALUES (?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx, $commentContent, $commentImgUrl]);

    $recruitId = $pdo->lastInsertId();
    $st = null;
    $pdo = null;

    return $recruitId;
}
function createCommentReply($userIdx, $postIdx, $commentIdx, $commentContent, $commentImgUrl)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO PostComment (userIdx, postIdx, parentCommentIdx, commentContents, commentImgUrl) VALUES (?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx, $commentIdx, $commentContent, $commentImgUrl]);

    $recruitId = $pdo->lastInsertId();
    $st = null;
    $pdo = null;

    return $recruitId;
}

function getPostIdxByCommentIdx($commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select postIdx from PostComment where commentIdx = ? and parentCommentIdx is null and isDeleted = 'N'";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["postIdx"]);
}
function editComment($commentContent, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE PostComment SET commentContents = ? WHERE commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$commentContent, $commentIdx]);

    $st = null;
    $pdo = null;
}
function isCommentOrReplyExist($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostComment WHERE commentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function deleteComment($commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE PostComment SET isDeleted = 'Y' WHERE commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);

    $st = null;
    $pdo = null;
}
function getCommentUserIdx($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM PostComment WHERE commentIdx = ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["userIdx"]);
}