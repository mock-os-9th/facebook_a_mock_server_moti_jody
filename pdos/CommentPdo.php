<?php
function getCommentLike($userIdx, $commentIdx, $page, $limit)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select count(*),
                       (select json_arrayagg(commentObj) from (
                        select json_object('userIdx', u.userIdx,
                            'userName', u.userName,
                            'userProfileImg', u.profileImgUrl,
                            'knowingFriendCount', (select (SELECT count(F.userIdx)
                                from Friends F
                                WHERE F.friendIdx = f.friendIdx and F.isDeleted = 'N' and F.userIdx != $userIdx
                                    AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N' or userIdx = 42 and Blocked.isDeleted = 'N'))
                            from Friends as f where isDeleted = 'N'
                                 AND friendIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N')
                                 and userIdx = $userIdx and f.friendIdx = cl.userIdx),
                            'isFriend',
                                        case
                                           when ((select exists(select * from Friends where userIdx = $userIdx and friendIdx = cl.userIdx and isDeleted = 'N')) = true)
                                               then 1
                                           when ((select exists(select * from Friends where userIdx = $userIdx and friendIdx = cl.userIdx and isDeleted = 'N')) = false and cl.userIdx = $userIdx)
                                               then 2
                                           else 0
                                        end
                            ) as commentObj
                        from CommentLike as cl
                            inner join (select userIdx, concat(firstName, ' ', secondName) as userName, profileImgUrl
                                        from User
                                        ) as u on u.userIdx = cl.userIdx
                
                        where cl.commentIdx = $commentIdx and isDeleted = 'N'
                        order by cl.createAt desc
                        limit $page, $limit
                        ) as list ) as commentLikedList
                from CommentLike
                where commentIdx = $commentIdx and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    foreach ($res as $key => $row) {
        $res[$key]['commentLikedList'] = json_decode($row['commentLikedList']);
    }

    return $res;
}

function isCommentLikeExist($commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM CommentLike WHERE commentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function likeComment($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE CommentLike SET isDeleted = 'Y' WHERE userIdx = ? and commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);

    $st = null;
    $pdo = null;
}
function modifyCommentLike($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE CommentLike SET isDeleted = 'N' WHERE userIdx = ? and commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);

    $st = null;
    $pdo = null;
}
function makeCommentLike($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "insert into CommentLike (userIdx, commentIdx) values (?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st = null;
    $pdo = null;
}

function isUserLikedComment($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM CommentLike WHERE userIdx =? and commentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isCommentLikeExistOnUser($userIdx, $commentIdx)
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

function showComment($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE UserCommentHide SET isDeleted = 'Y' WHERE userIdx = ? and commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);

    $st = null;
    $pdo = null;
}
function modifyCommentHide($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE UserCommentHide SET isDeleted = 'N' WHERE userIdx = ? and commentIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);

    $st = null;
    $pdo = null;
}
function makeCommentHide($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();

    $query = "insert into UserCommentHide (userIdx, commentIdx) values (?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st = null;
    $pdo = null;
}

function isUserHidedComment($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserCommentHide WHERE userIdx =? and commentIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isCommentHideExistOnUser($userIdx, $commentIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM UserCommentHide WHERE userIdx = ? and commentIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $commentIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function getNameFromIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT concat(firstName, ' ', secondName) as userName FROM User WHERE userIdx = ? and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["userName"]);
}

function send_comment_noti($userIdx, $postIdx, $commentContent)
{
    $pdo = pdoSqlConnect();

 //   $tokens = array();
    $query = "select u.token as token
            from User as u
                inner join (select userIdx from SettingPostNotification where postIdx = 816) as pc on pc.userIdx = u.userIdx;";

//    $res = mysqli_query($pdo, $query);
    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    $tokens = array(
        "token"     => intval($res[0]["token"])
    );
//    if(mysqli_num_rows($res) > 0 ){
//        while ($row = mysqli_fetch_assoc($res)) {
//            $tokens[] = $row['token'];
//        }
//    } else {
//        echo 'There are no Transfer Data';
//        exit;
//    }

//            $result = $this->lib['db']->query($sql);
//            while($row = $this->lib['db']->result_assoc($result))
//            {
//                $tokens[] = $row['token'];
//            }

    $userName = getNameFromIdx($userIdx);
    $message = array(
        "title"     => $userName."이 댓글을 남겻습니다",
        "body"   => $commentContent
        //"link"      => URL . "post/816/comment?page=1&limit=5" . $last_idx
    );
    //print_r($message);exit;
    send_notification($tokens, $message);
}
function send_notification($tokens, $message)
{
    $GOOGLE_API_KEY = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";
    $url = 'https://fcm.googleapis.com/fcm/send';

    $fields = array(
        'to' => $tokens,
        'notification'             => $message
    );

//    $headers = array(
//        'Authorization:key =' . self::GOOGLE_FCM_API_KEY,
//        'Content-Type: application/json'
//    );
    /*
    print_r($fields);
    print_r($headers);
    exit;
    */

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
//    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
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