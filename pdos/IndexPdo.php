<?php

function createUser($email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender)
{
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO User (email, phoneNum, pwd, secondName, firstName, bday, gender) VALUES (?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender]);

    $recruitId = $pdo->lastInsertId();
    $st = null;
    $pdo = null;

    return $recruitId;
}

function isEmailDuplicated($email)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE email = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$email]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isPhoneNumDuplicate($phoneNum)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM User WHERE phoneNum = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$phoneNum]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function isValidUser($id, $pw)
{
    $pdo = pdoSqlConnect();

    if (isValidPhoneNum($id)) {
        $query = "SELECT EXISTS(SELECT * FROM User WHERE phoneNum= ? AND pwd = ? AND isDeleted = 'N') AS exist;";
    } else if (isValidEmail($id)) {
        $query = "SELECT EXISTS(SELECT * FROM User WHERE email= ? AND pwd = ? AND isDeleted = 'N') AS exist;";
    } else {
        return false;
    }
    $st = $pdo->prepare($query);
    $st->execute([$id, $pw]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function deleteUser($id)
{
    $pdo = pdoSqlConnect();

    if (isValidPhoneNum($id)) {
        $query = "UPDATE User SET isDeleted = 'Y' WHERE phoneNum = ?;";
    } else if (isValidEmail($id)) {
        $query = "UPDATE User SET isDeleted = 'Y' WHERE email = ?;";
    } else {
        return false;
    }
    $st = $pdo->prepare($query);
    $st->execute([$id]);

    $st = null;
    $pdo = null;

    return $id;
}

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

function getUserFriendList($idx)
{
    $pdo = pdoSqlConnect();
    $query = "select f.friendIdx,
                   concat(u.firstName, ' ', u.secondName) as userName,
                   u.profileImgUrl,
                   (SELECT count(F.userIdx)
                    from Friends F
                    WHERE F.userIdx IN (SELECT userIdx FROM Friends WHERE Friends.friendIdx = f.friendIdx)
                    AND F.friendIdx = $idx) as knowingFriendCount
            from Friends as f
                inner join (select userIdx, firstName, secondName, profileImgUrl from User) as u on u.userIdx = f.friendIdx
            where f.userIdx = $idx;";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

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

function getMainFeed($page, $limit, $userIdx)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select Posts.postType,
       UserName.userIdx                                        as userIdx,
       UserName.name                                           as userName,
       WriterName.userIdx                                      as writerIdx,
       WriterName.name                                         as writerName,
       if(timestampdiff(minute, Posts.createAt, now()) > 60, if(timestampdiff(hour, Posts.createAt, now()) > 24,
                                                          if(timestampdiff(day, Posts.createAt, now()) > 30,
                                                             concat(timestampdiff(month, Posts.createAt, now()), '개월'),
                                                             concat(timestampdiff(day, Posts.createAt, now()), '일')),
                                                          concat(timestampdiff(hour, Posts.createAt, now()), '시간')),
          concat(timestampdiff(minute, Posts.createAt, now()), '분')) as lastTime,
       if(moodActivity = 'M', '기분', '활동')                      as moodActivity,
       Mood.moodName,
       Mood.moodImgUrl,
       Activity.activityName,
       Activity.activityImgUrl,
       Activity.activityContents,
       Posts.postContents,
       Posts.postSharedType                                    as sharedPostType,
       Posts.postSharedIdx,
       UserName.postIdx,
       imgVodList,
       likeCount,
       likeImgList,
       commentCount,
       sharedCount,
       if(UserPostHide.userIdx = $userIdx,'Y','N') as isHided,
       if(PostLike.userIdx = $userIdx,'Y','N') as isLiked,
       if(UserPostSaved.userIdx = $userIdx,'Y','N') as isSaved,
       if(SettingPostNotification.userIdx = $userIdx,'Y','N') as isNotificated
from Posts
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.userIdx
      from Posts
               left outer join User on User.userIdx = Posts.userIdx) as UserName on Posts.postIdx = UserName.postIdx
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.userIdx
      from Posts
               left outer join User on User.userIdx = Posts.writerIdx) as WriterName
     on Posts.postIdx = WriterName.postIdx
         left outer join (select moodName, moodImgUrl, postIdx
                          from PostMood
                                   left outer join MoodCategory on PostMood.moodIdx = MoodCategory.moodIdx) as Mood
                         on Mood.postIdx = Posts.postIdx
         left outer join (select activityName, activityContents, activityImgUrl, postIdx
                          from PostActivity
                                   left outer join ActivityCategory
                                                   on PostActivity.activityIdx = ActivityCategory.activityIdx) as Activity
                         on Activity.postIdx = Posts.postIdx
         left outer join (select json_arrayagg(json_object('imgVodUrl', Posts.postImgVideoUrl, 'imgVodContents',
                                                           Posts.postContents, 'imgVodLikeCount',
                                                           ImgVideoLike.imgVodLikeCount, 'imgVodCommentCount',
                                                           imgVodCommentCount, 'imgVodSharedCount', imgVodSharedCount,
                                                           'isImgVodLiked', isLiked, 'imgVodLikeImgList',
                                                           ImgVideoLike.imgVodLikeImgList)) as imgVodList,
                                 PostImgVideo.postIdx
                          from PostImgVideo
                                   left outer join Posts on imgVideoPostIdx = Posts.postIdx
                                   left outer join (select imgVideoPostIdx,
                                                           count(*)                                as imgVodLikeCount,
                                                           json_arrayagg(LikeCategory.likeIconUrl) as imgVodLikeImgList,
                                                           if(PostLike.userIdx = 1, 'Y', 'N')      as isLiked
                                                    from PostImgVideo
                                                             left outer join PostLike on PostLike.postIdx = PostImgVideo.imgVideoPostIdx
                                                             left outer join LikeCategory on likeIdx = PostLike.postLikeIdx
                                                    group by imgVideoPostIdx) as ImgVideoLike
                                                   on ImgVideoLike.imgVideoPostIdx = PostImgVideo.imgVideoPostIdx
                                   left outer join (select imgVideoPostIdx, count(*) as imgVodCommentCount
                                                    from PostImgVideo
                                                             left outer join PostComment on imgVideoPostIdx = PostComment.postIdx
                                                    group by imgVideoPostIdx) as ImgVideoComment
                                                   on ImgVideoComment.imgVideoPostIdx = PostImgVideo.imgVideoPostIdx
                                   left outer join (select imgVideoPostIdx, count(*) as imgVodSharedCount
                                                    from PostImgVideo
                                                             left outer join PostShared on imgVideoPostIdx = PostShared.postIdx
                                                    group by imgVideoPostIdx) as ImgVideoShared
                                                   on ImgVideoShared.imgVideoPostIdx = PostImgVideo.imgVideoPostIdx
                          group by PostImgVideo.postIdx) as imgVodList on imgVodList.postIdx = Posts.postIdx
left outer join (select Posts.postIdx, count(*) as likeCount, json_arrayagg(LikeCategory.likeIconUrl) as likeImgList from Posts left outer join PostLike on Posts.postIdx = PostLike.postIdx left outer join LikeCategory on LikeCategory.likeIdx = PostLike.postLikeIdx group by postIdx) as PostLikeCount on PostLikeCount.postIdx = Posts.postIdx
left outer join (select Posts.postIdx, count(*) as commentCount from Posts left outer join PostComment on Posts.postIdx = PostComment.postIdx group by Posts.postIdx) as PostCommentCount on PostCommentCount.postIdx = Posts.postIdx
left outer join (select Posts.postIdx, count(*) as sharedCount from Posts left outer join PostShared on Posts.postIdx = PostShared.postIdx group by Posts.postIdx) as PostSharedCount on PostSharedCount.postIdx = Posts.postIdx
left outer join UserPostHide on UserPostHide.postIdx = Posts.postIdx
left outer join PostLike on PostLike.postIdx = Posts.postIdx
left outer join UserPostSaved on UserPostSaved.postIdx = Posts.postIdx
left outer join SettingPostNotification on SettingPostNotification.postIdx = Posts.postIdx
where if(postPrivacyBounds = 'E', true = (select bit_and(if(PrivacyBoundExcept.userIdx = $userIdx,false,true))
                                           from PrivacyBoundExcept
                                           where Posts.postIdx = PrivacyBoundExcept.idx
                                             and PrivacyBoundExcept.exceptApplyType = 'P'group by PrivacyBoundExcept.idx), true)
  and if(postPrivacyBounds = 'S', true = (select bit_or(if(PrivacyBoundShow.userIdx = $userIdx,true,false))
                                           from PrivacyBoundShow
                                           where Posts.postIdx = PrivacyBoundShow.idx
                                             and PrivacyBoundShow.showApplyType = 'P'), true) 
  and if(postPrivacyBounds = 'M', $userIdx = Posts.writerIdx,true)
  and if(postPrivacyBounds = 'F', Posts.writerIdx = (select friendIdx from Friends where userIdx = $userIdx),true)
order by Posts.createAt desc
limit $page,$limit;";
    $st = $pdo->prepare($query);
    $st->execute();

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    foreach ($res as $key => $row) {
        $res[$key]['imgVodList'] = json_decode($row['imgVodList']);
        $res[$key]['likeImgList'] = json_decode($row['likeImgList']);
    }

    $st = null;
    $pdo = null;

    return $res;
}

function isValidMoodIdx($moodIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from PostMood where moodIdx = ?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$moodIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function isValidActivityIdx($activityIdx)
{
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from PostActivity where activityIdx = ?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$activityIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function createPost($userIdx, $feedUserIdx, $postPrivacyBound, $postContents, $moodActivity, $moodIdx, $activityIdx, $activityContents, $imgVodList, $friendExcept, $friendShow)
{
    $pdo = pdoSqlConnect();

    $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,moodActivity) values ('P',?,?,?,?,?);";
    echo $feedUserIdx, $userIdx, $postPrivacyBound, $postContents, $moodActivity;
    $st = $pdo->prepare($query);
    $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $postContents, $moodActivity]);

    $mainPostIdx = $pdo->lastInsertId();

    if ($moodActivity == 'M') {
        $query = "insert into PostMood(moodIdx, postIdx) VALUES (?,?)";

        $st = $pdo->prepare($query);
        $st->execute([$moodIdx, $mainPostIdx]);
    }
    if ($moodActivity == 'A') {
        $query = "insert into PostActivity(activityIdx, postIdx,activityContents) VALUES (?,?,?)";

        $st = $pdo->prepare($query);
        $st->execute([$activityIdx, $mainPostIdx, $activityContents]);
    }


    if ($postPrivacyBound == 'E') {
        foreach ($friendExcept as $key => $item) {
            $query = "insert into PrivacyBoundExcept(idx, userIdx, exceptApplyType) VALUES (?,?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$mainPostIdx, $item, 'P']);
        }
    }
    if ($postPrivacyBound == 'S') {
        foreach ($friendShow as $key => $item) {
            $query = "insert into PrivacyBoundShow(idx, userIdx, showApplyType) VALUES (?,?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$mainPostIdx, $item, 'P']);
        }
    }
    foreach ($imgVodList as $key => $item) {
        $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,postImgVideoUrl) values ('I',?,?,?,?,?)";

        $st = $pdo->prepare($query);
        $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $item->imgVodContents, $item->imgVodUrl]);

        $imgPostIdx = $pdo->lastInsertId();

        $query = "insert into PostImgVideo(postIdx,imgVideoPostIdx) values (?,?)";

        $st = $pdo->prepare($query);
        $st->execute([$mainPostIdx, $imgPostIdx]);
    }
}

////READ
//function testDetail($testNo)
//{
//    $pdo = pdoSqlConnect();
//    $query = "SELECT * FROM Test WHERE no = ?;";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$testNo]);
//    //    $st->execute();
//    $st->setFetchMode(PDO::FETCH_ASSOC);
//    $res = $st->fetchAll();
//
//    $st = null;
//    $pdo = null;
//
//    return $res[0];
//}
//
//
//function testPost($name)
//{
//    $pdo = pdoSqlConnect();
//    $query = "INSERT INTO Test (name) VALUES (?);";
//
//    $st = $pdo->prepare($query);
//    $st->execute([$name]);
//
//    $st = null;
//    $pdo = null;
//
//}

// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
