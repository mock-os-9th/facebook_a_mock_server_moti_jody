<?php

$pdo = pdoSqlConnect();
$query = "select UserFriends.userIdx,UserFriends.friendIdx, FriendFriend.friendIdx, count(FriendFriend.friendIdx) as count
from (select Friends.userIdx, Friends.friendIdx
      from User
               left outer join Friends on User.userIdx = Friends.userIdx where User.isDeleted = 'N' or Friends.isDeleted = 'N') as UserFriends
         left outer join Friends as FriendFriend on UserFriends.friendIdx = FriendFriend.userIdx
         where not UserFriends.userIdx = FriendFriend.friendIdx and not (UserFriends.userIdx,FriendFriend.friendIdx) in (select UserFriends.userIdx,friendIdx from Friends where UserFriends.userIdx = Friends.userIdx)
group by UserFriends.userIdx,FriendFriend.friendIdx
order by count desc";
echo 'hello1';

$st = $pdo->prepare($query);
$st->execute();
$st->setFetchMode(PDO::FETCH_ASSOC);
$friendList = $st->fetchAll();
echo 'hello2';

foreach ($friendList as $key => $item) {
    $userIdx = $item['userIdx'];
    $recommendUserIdx = $item['recommendUserIdx'];

    echo $userIdx, $recommendUserIdx;

    $query = "select exists (select * from FriendRecommend where userIdx = $userIdx and recommendUserIdx = $recommendUserIdx and isDeleted = 'N') as exist";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $isDuplication = $st->fetchAll();
    $isDuplication = intval($isDuplication[0]['exist']);

    echo "hello3";


    $query = "select exists (select * from FriendRecommend where userIdx = $userIdx and recommendUserIdx = $recommendUserIdx and isDeleted = 'Y') as exist";
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $isDuplication = $st->fetchAll();
    $wasExist = intval($isDuplication[0]['exist']);

    echo "hell4";

    if ($isDuplication == 0 && $wasExist == 0) {
        $query = "insert into FriendRecommend (userIdx,recommendUserIdx) values ($userIdx,$recommendUserIdx)";
        $st = $pdo->prepare($query);
        $st->execute();
    } else if ($isDuplication == 0 && $wasExist == 1) {
        $query = "update FriendRecommend set isDeleted = 'N' where userIdx = $userIdx, recommendUserIdx = $recommendUserIdx";
        $st = $pdo->prepare($query);
        $st->execute();
    }
}