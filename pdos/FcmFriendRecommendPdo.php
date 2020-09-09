<?php
function pdoSqlConnect()
{
    try {
        $DB_HOST = "facebookdev.cevu32mso1il.ap-northeast-2.rds.amazonaws.com";
        $DB_NAME = "facebookDev";
        $DB_USER = "facebookDev";
        $DB_PW = "lyunj2020!";

        $GOOGLE_API_KEY = "AAAAuTKmVM0:APA91bHwf4e40fq1oq9nYUoMAGE12AlpZ58WViaQdsEqYqTqHVdV7zimDMTJvp7GjkdhSXI1qp8gH_qhMl8ooyOjsJqf4SDOHbV3avyguHijNat-aG_wsxQKyJP_NBKWcKkYDhgtN4Ob";

        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}

$pdo = pdoSqlConnect();
$query = "select UserFriends.userIdx,UserFriends.friendIdx, FriendFriend.friendIdx, count(FriendFriend.friendIdx) as count
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

    echo $userIdx, $recommendUserIdx;

    $query = "select exists (select * from FriendRecommend where userIdx = ? and recommendUserIdx = ? and isDeleted = 'N') as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$recommendUserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $isDuplication = $st->fetchAll();
    $isDuplication = intval($isDuplication[0]['exist']);


    $query = "select exists (select * from FriendRecommend where userIdx = ? and recommendUserIdx = ? and isDeleted = 'Y') as exist";
    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$recommendUserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $isDuplication = $st->fetchAll();
    $wasExist = intval($isDuplication[0]['exist']);

    if ($isDuplication == 0 && $wasExist == 0) {
        $query = "insert into FriendRecommend (userIdx,recommendUserIdx) values (?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$recommendUserIdx]);
    } else if ($isDuplication == 0 && $wasExist == 1) {
        $query = "update FriendRecommend set isDeleted = 'N' where userIdx = ?, recommendUserIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$recommendUserIdx]);
    }
}