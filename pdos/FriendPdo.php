<?php

function getUserFriendList($idx, $targetIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select concat(u.firstName, ' ', u.secondName) as userName,
                   (select count(userIdx)
                   from Friends
                   where userIdx = $targetIdx
                     and friendIdx not in (select blockedUserIdx from Blocked where userIdx = $idx or userIdx = $targetIdx)) as friendCount,
                   (select json_arrayagg(friendobj) from (
                    select json_object('friendIdx', f.friendIdx,
                            'friendName', concat(u.firstName, ' ', u.secondName),
                           'friendImgUrl', u.profileImgUrl,
                           'knowingFriendCount', (SELECT count(F.userIdx)
                            from Friends F
                            WHERE F.userIdx IN (SELECT userIdx
                            FROM Friends
                            WHERE Friends.friendIdx = f.friendIdx
                                AND userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx or userIdx = $targetIdx))
                            AND F.friendIdx = $targetIdx),
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
                                    where userIdx not in (select blockedUserIdx from Blocked where userIdx = $idx or useridx = $targetIdx)
                                    ) as u on u.userIdx = f.friendIdx
                    where f.userIdx = $targetIdx
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

    $query = "INSERT INTO Blocked (useridx, blockedUserIdx) VALUES (?, ?);";

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