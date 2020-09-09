<?php

require '/var/www/html/api/pdos/DatabasePdo.php';

$pdo = pdoSqlConnect();
$query = "select UserFriends.userIdx,UserFriends.friendIdx, FriendFriend.friendIdx as recommendUserIdx, count(FriendFriend.friendIdx) as count
from (select Friends.userIdx, Friends.friendIdx
      from User
               left outer join Friends on User.userIdx = Friends.userIdx where User.isDeleted = 'N' or Friends.isDeleted = 'N') as UserFriends
         left outer join Friends as FriendFriend on UserFriends.friendIdx = FriendFriend.userIdx
         where not UserFriends.userIdx = FriendFriend.friendIdx and not (UserFriends.userIdx,FriendFriend.friendIdx) in (select UserFriends.userIdx,friendIdx from Friends where UserFriends.userIdx = Friends.userIdx)
group by UserFriends.userIdx,FriendFriend.friendIdx
order by count desc";

$st = $pdo->prepare($query);
$st->execute();
$st->setFetchMode(PDO::FETCH_ASSOC);
$friendList = $st->fetchAll();

foreach ($friendList as $key => $item) {
    $userIdx = $item['userIdx'];
    $recommendUserIdx = $item['recommendUserIdx'];
    $count = $item['count'];

    $query = "select exists (select * from FriendRecommend where userIdx = ? and recommendUserIdx = ? and isDeleted = 'N') as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $recommendUserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $isDuplication = $st->fetchAll();
    $isDuplication = intval($isDuplication[0]['exist']);


    $query = "select exists (select * from FriendRecommend where userIdx = ? and recommendUserIdx = ? and isDeleted = 'Y') as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $recommendUserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $wasExist = $st->fetchAll();
    $wasExist = intval($wasExist[0]['exist']);

    if ($isDuplication == 0 && $wasExist == 0) {
        $query = "insert into FriendRecommend (userIdx,recommendUserIdx,count) values (?,?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx, $recommendUserIdx,$count]);
    } else if ($isDuplication == 0 && $wasExist == 1) {
        $query = "update FriendRecommend set count = ? isDeleted = 'N' where userIdx = ?, recommendUserIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$count,$userIdx, $recommendUserIdx]);
    }
}