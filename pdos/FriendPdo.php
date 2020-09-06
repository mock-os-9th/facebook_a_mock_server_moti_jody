<?php

function requestFriend($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO FriendRequest (senderIdx, receiverIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function updateRequestFriend($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE FriendRequest SET isDeleted = 'N' WHERE senderIdx = ? and receiverIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $recruitId = $pdo->lastInsertId();
    $st = null;
    $pdo = null;

    return $recruitId;
}

function isRequestedFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM FriendRequest WHERE senderIdx = ? and receiverIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isAcceptedOrDeletedBefore($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM FriendRequest WHERE senderIdx = ? and receiverIdx = ? and isDeleted = 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function acceptFriendRequest($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE FriendRequest SET isDeleted = 'Y' WHERE senderIdx = ? and receiverIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}

function addFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO Friends (userIdx, friendIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function addFollowing($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO Following (userIdx, followingUserIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function unDeleteFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "UPDATE Friends SET isDeleted = 'N' WHERE userIdx = ? and friendIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function rejectFriendRequest($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE FriendRequest SET isDeleted = 'Y' WHERE senderIdx = ? and receiverIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function getUserFriendList($userIdx, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select concat(u.firstName, ' ', u.secondName) as userName,
               (select count(userIdx)
               from Friends
               where userIdx = $targetIdx and Friends.isDeleted = 'N'
                 and friendIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')) as friendCount,
               (select json_arrayagg(friendobj) from (
                select json_object('friendIdx', f.friendIdx,
                        'friendName', concat(u.firstName, ' ', u.secondName),
                       'friendImgUrl', u.profileImgUrl,
                       'knowingFriendCount', (SELECT count(F.userIdx)
                        from Friends F
                        WHERE F.friendIdx = f.friendIdx and F.isDeleted = 'N' and F.userIdx != $userIdx
                            AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                        ),
                        'isFriend',
                                    case
                                       when ((select exists(select * from Friends where userIdx = $userIdx and friendIdx = f.friendIdx and isDeleted = 'N')) = true)
                                           then 1
                                       when ((select exists(select * from Friends where userIdx = $userIdx and friendIdx = f.friendIdx and isDeleted = 'N')) = false and f.friendIdx = $userIdx)
                                           then 2
                                       else 0
                                    end
                    ) as friendobj
                from Friends as f
                    inner join (select userIdx, firstName, secondName, profileImgUrl
                                from User
                                where userIdx not in (select blockedUserIdx from Blocked where userIdx = $userIdx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                                and isDeleted = 'N'
                                ) as u on u.userIdx = f.friendIdx
                where f.userIdx = $targetIdx and f.isDeleted = 'N'
                order by u.firstName
                ) as friendArray) as friendList
        from User as u
        where u.userIdx = $targetIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    foreach ($res as $key => $row) {
        $res[$key]['friendList'] = json_decode($row['friendList']);
    }
    return $res[0];
}
function friendExist($idx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Friends WHERE userIdx = ? and isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function blockUser($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO Blocked (userIdx, blockedUserIdx) VALUES (?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}

function isBlockedFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Blocked WHERE userIdx = ? AND blockedUserIdx = ? AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Friends WHERE userIdx = ? AND friendIdx = ? AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function followUser($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE Following SET isDeleted = 'N' WHERE userIdx = ? AND followingUserIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function unfollowUser($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();

    $query = "UPDATE Following SET isDeleted = 'Y' WHERE userIdx = ? AND followingUserIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}
function isFollowedFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Following WHERE userIdx = ? AND followingUserIdx = ? AND isDeleted = 'N') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function isUnFollowedFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Following WHERE userIdx = ? AND followingUserIdx = ? AND isDeleted = 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function deleteFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "UPDATE Friends SET isDeleted = 'Y' WHERE userIdx = ? and friendIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);

    $st = null;
    $pdo = null;
}

function isDeletedFriend($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(SELECT * FROM Friends WHERE userIdx = ? AND friendIdx = ? AND isDeleted = 'Y') AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function getKnownFriendList($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select concat(u.firstName, ' ', u.secondName) as userName,
                   (select count(userIdx) from Friends
                   where userIdx = $targetIdx and friendIdx != $idx and Friends.isDeleted = 'N'
                   AND friendIdx in
                       (select friendIdx from Friends
                       where userIdx = $idx and Friends.isDeleted = 'N' AND Friends.friendIdx not in
                                              (select blockedUserIdx
                                              from Blocked
                                              where userIdx = $idx and Blocked.isDeleted = 'N'
                                                 or useridx = $targetIdx and Blocked.isDeleted = 'N')
                       )
                    ) as friendCount,
                   (select json_arrayagg(friendobj)
                   from (select json_object('friendIdx', f.friendIdx,
                       'friendName', concat(u.firstName, ' ', u.secondName),
                       'friendImgUrl', u.profileImgUrl,
                       'knowingFriendCount', (SELECT count(F.userIdx)
                        from Friends F
                        WHERE F.friendIdx = f.friendIdx and F.isDeleted = 'N'
                          AND userIdx not in
                              (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                          AND F.userIdx != $idx)
                        ) as friendobj
                    from Friends as f
                        inner join (select userIdx, firstName, secondName, profileImgUrl
                                    from User
                                    where userIdx != $idx
                                        AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                                    ) as u on u.userIdx = f.friendIdx
                    where f.userIdx = $targetIdx and f.isDeleted = 'N'
                        AND f.friendIdx in (
                            select friendIdx
                            from Friends
                            where userIdx = $idx and Friends.isDeleted = 'N'
                              AND Friends.friendIdx not in (
                                  select blockedUserIdx
                                  from Blocked
                                  where userIdx = $idx and Blocked.isDeleted = 'N'
                                     or userIdx = $targetIdx and Blocked.isDeleted = 'N'
                                  )
                            )
                    order by u.firstName
                    ) as friendArray) as friendList
            from User as u
            where u.userIdx = $targetIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    foreach ($res as $key => $row) {
        $res[$key]['friendList'] = json_decode($row['friendList']);
    }
    return $res[0];
}
function isKnownFriendExist($idx, $targetIdx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(select friendIdx from Friends
               where userIdx = $targetIdx and friendIdx != $idx and isDeleted = 'N'
               AND friendIdx in
                   (select friendIdx from Friends
                   where userIdx = $idx and isDeleted = 'N'
                   AND Friends.friendIdx not in
                   (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N'))
                   ) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}
function getRequestedFriendList($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select count(*),
               (select json_arrayagg(friendobj)
               from (select json_object('friendIdx', fr.senderIdx,
                   'friendName', concat(u.firstName, ' ', u.secondName),
                   'friendImgUrl', u.profileImgUrl,
                   'knowingFriendCount', (SELECT count(F.userIdx)
                    from Friends F
                    WHERE F.friendIdx = fr.senderIdx and F.isDeleted = 'N'
                      AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N')
                      AND F.userIdx != $idx),
                   'requestedDate',
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
                          end
                    ) as friendobj
                from FriendRequest as fr
                    inner join (select userIdx, firstName, secondName, profileImgUrl
                                from User
                                where userIdx != $idx and isDeleted = 'N'
                                    AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N')
                                ) as u on u.userIdx = fr.senderIdx
                where fr.receiverIdx = $idx and fr.isDeleted = 'N'
                order by createAt desc
                ) as friendArray) as friendRequestList
        from FriendRequest
        where receiverIdx = $idx and isDeleted = 'N';";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    foreach ($res as $key => $row) {
        $res[$key]['friendRequestList'] = json_decode($row['friendRequestList']);
    }
    return $res[0];
}
function isRequestedFriendExist($idx) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(select senderIdx from FriendRequest
                   where receiverIdx = $idx and isDeleted = 'N'
                   AND senderIdx not in
                       (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N')
                ) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function searchFriend($idx, $targetIdx, $keyword)
{
    $pdo = pdoSqlConnect();
    $query = "select concat(u.firstName, ' ', u.secondName) as userName,
               (select json_arrayagg(friendobj) from (
                select json_object('friendIdx', f.friendIdx,
                        'friendName', concat(u.firstName, ' ', u.secondName),
                       'friendImgUrl', u.profileImgUrl,
                       'knowingFriendCount', (SELECT count(F.userIdx)
                        from Friends F
                        WHERE F.userIdx IN (SELECT userIdx
                        FROM Friends
                        WHERE Friends.friendIdx = f.friendIdx
                            AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N'))
                        AND F.friendIdx = f.friendIdx AND F.userIdx != $idx),
                        'isFriend', (
                                       case
                                           when ((select exists(select *
                                                                from Friends
                                                                where userIdx = $idx
                                                                  and friendIdx = f.friendIdx
                                                                  and isDeleted = 'N')) = true)
                                               then 1
                                           when ((select exists(select *
                                                                from Friends
                                                                where userIdx = $idx
                                                                  and friendIdx = f.friendIdx
                                                                  and isDeleted = 'N')) = false and f.friendIdx = $idx)
                                               then 2
                                           else 0
                                           end
                                       )
                    ) as friendobj
                from Friends as f
                    inner join (select userIdx, firstName, secondName, profileImgUrl
                                from User
                                where userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                                ) as u on u.userIdx = f.friendIdx
                where f.userIdx = $targetIdx
                and (u.firstName like concat('%', $keyword, '%') or u.secondName like concat('%', $keyword, '%'))
                order by u.firstName
                ) as friendArray) as friendList
        from User as u
        where u.userIdx = $targetIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx, $keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    foreach ($res as $key => $row) {
        $res[$key]['friendList'] = json_decode($row['friendList']);
    }
    return $res[0];
}
function friendExistWithKeyword($idx, $targetIdx, $keyword) {
    $pdo = pdoSqlConnect();

    $query = "SELECT EXISTS(
                (select *
                from Friends as f
                    inner join (select *
                                from User
                                where userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx and Blocked.isDeleted = 'N' or userIdx = $targetIdx and Blocked.isDeleted = 'N')
                                ) as u on u.userIdx = f.friendIdx
                where f.userIdx = $targetIdx
                and (u.firstName like concat('%', $keyword, '%') or u.secondName like concat('%', $keyword, '%'))
                order by u.firstName
                )) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$idx, $targetIdx, $keyword]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}