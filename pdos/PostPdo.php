<?php

function getMainFeed($page, $limit, $userIdx)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select Posts.postType,
       UserName.userIdx                                              as userIdx,
       UserName.name                                                 as userName,
       WriterName.writerIdx                                            as writerIdx,
       WriterName.name                                               as writerName,
       case
            when timestampdiff(month , Posts.createAt, now()) > 6 then concat(year(Posts.createAt),'년',month(Posts.createAt),'달',day(Posts.createAt),'일')
           when timestampdiff(day , Posts.createAt, now()) > 30 then concat(timestampdiff(month , Posts.createAt, now()),'달 전')
           when timestampdiff(hour , Posts.createAt, now()) > 24 then concat(timestampdiff(day , Posts.createAt, now()),'일 전')
           when timestampdiff(minute , Posts.createAt, now()) > 60 then concat(timestampdiff(hour , Posts.createAt, now()),'시간 전')
           when timestampdiff(second, Posts.createAt, now()) > 60 then concat(timestampdiff(minute , Posts.createAt, now()),'분 전')
           else concat(timestampdiff(second, Posts.createAt, now()),'초 전')
           end
           as lastTime,
       if(moodActivity = 'M', '기분', '활동')                            as moodActivity,
       Mood.moodName,
       Mood.moodImgUrl,
       Activity.activityName,
       Activity.activityImgUrl,
       Activity.activityContents,
       Posts.postContents,
       Posts.postImgVideoUrl,
       Posts.postImgVideoType,
       Posts.postSharedType                                          as sharedPostType,
       Posts.postSharedIdx,
       UserName.postIdx,
       imgVodList,
       likeCount,
       likeImgList,
       commentCount,
       sharedCount,
       if(UserPostHide.userIdx = $userIdx, 'Y', 'N')                        as isHided,
       if(PostLike.userIdx = $userIdx, 'Y', 'N')                            as isLiked,
       if(UserPostSaved.userIdx = $userIdx, 'Y', 'N')                       as isSaved,
       if(SettingPostNotification.userIdx = $userIdx, 'Y', 'N')             as isNotificated

from Posts
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.userIdx
      from Posts
               left outer join User on User.userIdx = Posts.userIdx) as UserName on Posts.postIdx = UserName.postIdx
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.writerIdx
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
         left outer join (select json_arrayagg(json_object('imgVodPostIdx',Posts.postIdx,'imgVodUrl', Posts.postImgVideoUrl,'imgVodType',Posts.postImgVideoType,'imgVodContents',
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
                                                           if(PostLike.userIdx = $userIdx, 'Y', 'N')      as isLiked
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
                                                   where Posts.isDeleted = 'N'
                          group by PostImgVideo.postIdx) as imgVodList on imgVodList.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx,
                                 count(*)                                as likeCount,
                                 json_arrayagg(LikeCategory.likeIconUrl) as likeImgList
                          from Posts
                                   left outer join PostLike on Posts.postIdx = PostLike.postIdx
                                   left outer join LikeCategory on LikeCategory.likeIdx = PostLike.postLikeIdx
                          group by postIdx) as PostLikeCount on PostLikeCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as commentCount
                          from Posts
                                   left outer join PostComment on Posts.postIdx = PostComment.postIdx
                          group by Posts.postIdx) as PostCommentCount on PostCommentCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as sharedCount
                          from Posts
                                   left outer join PostShared on Posts.postIdx = PostShared.postIdx
                          group by Posts.postIdx) as PostSharedCount on PostSharedCount.postIdx = Posts.postIdx
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
  and if(postPrivacyBounds = 'F', (select bit_or(Posts.writerIdx = friendIdx) from Friends where userIdx = 1 group by Friends.userIdx),true)
  and Posts.postType = 'P'
  and Posts.isDeleted = 'N'
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

    $query = "select exists(select * from MoodCategory where moodIdx = ?) as exist";

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

    $query = "select exists(select * from ActivityCategory where activityIdx = ?) as exist";

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
    if (count($imgVodList) > 1) {
        $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,moodActivity) values ('P',?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $postContents, $moodActivity]);

        $mainPostIdx = $pdo->lastInsertId();
    } else if (count($imgVodList) == 1) {
        $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,postImgVideoUrl,postImgVideoType,moodActivity) values ('P',?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $postContents, $imgVodList[0]->imgVodUrl, $imgVodList[0]->imgVodType, $moodActivity]);

        $mainPostIdx = $pdo->lastInsertId();
    } else {
        $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,moodActivity) values ('P',?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $postContents, $moodActivity]);

        $mainPostIdx = $pdo->lastInsertId();
    }


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
    if (count($imgVodList) > 1) {
        foreach ($imgVodList as $key => $item) {
            $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,postImgVideoUrl,postImgVideoType) values ('I',?,?,?,?,?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$feedUserIdx, $userIdx, $postPrivacyBound, $item->imgVodContents, $item->imgVodUrl, $item->imgVodType]);

            $imgPostIdx = $pdo->lastInsertId();

            $query = "insert into PostImgVideo(postIdx,imgVideoPostIdx) values (?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$mainPostIdx, $imgPostIdx]);
        }
    }
}

function getPersonalFeed($page, $limit, $isFilter, $date, $writerType, $userIdx, $searchIdx)
{
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    $query = "select Posts.postType,
       UserName.userIdx                                              as userIdx,
       UserName.name                                                 as userName,
       WriterName.writerIdx                                            as writerIdx,
       WriterName.name                                               as writerName,
       case
            when timestampdiff(month , Posts.createAt, now()) > 6 then concat(year(Posts.createAt),'년',month(Posts.createAt),'달',day(Posts.createAt),'일')
           when timestampdiff(day , Posts.createAt, now()) > 30 then concat(timestampdiff(month , Posts.createAt, now()),'달 전')
           when timestampdiff(hour , Posts.createAt, now()) > 24 then concat(timestampdiff(day , Posts.createAt, now()),'일 전')
           when timestampdiff(minute , Posts.createAt, now()) > 60 then concat(timestampdiff(hour , Posts.createAt, now()),'시간 전')
           when timestampdiff(second, Posts.createAt, now()) > 60 then concat(timestampdiff(minute , Posts.createAt, now()),'분 전')
           else concat(timestampdiff(second, Posts.createAt, now()),'초 전')
           end
           as lastTime,
       if(moodActivity = 'M', '기분', '활동')                            as moodActivity,
       Mood.moodName,
       Mood.moodImgUrl,
       Activity.activityName,
       Activity.activityImgUrl,
       Activity.activityContents,
       Posts.postContents,
       Posts.postImgVideoUrl,
       Posts.postImgVideoType,
       Posts.postSharedType                                          as sharedPostType,
       Posts.postSharedIdx,
       UserName.postIdx,
       imgVodList,
       likeCount,
       likeImgList,
       commentCount,
       sharedCount,
       if(UserPostHide.userIdx = $userIdx, 'Y', 'N')                        as isHided,
       if(PostLike.userIdx = $userIdx, 'Y', 'N')                            as isLiked,
       if(UserPostSaved.userIdx = $userIdx, 'Y', 'N')                       as isSaved,
       if(SettingPostNotification.userIdx = $userIdx, 'Y', 'N')             as isNotificated

from Posts
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.userIdx
      from Posts
               left outer join User on User.userIdx = Posts.userIdx) as UserName on Posts.postIdx = UserName.postIdx
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.writerIdx
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
         left outer join (select json_arrayagg(json_object('imgVodPostIdx',Posts.postIdx,'imgVodUrl', Posts.postImgVideoUrl,'imgVodType',Posts.postImgVideoType,'imgVodContents',
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
                                                           if(PostLike.userIdx = $userIdx, 'Y', 'N')      as isLiked
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
                                                   where Posts.isDeleted = 'N'
                          group by PostImgVideo.postIdx) as imgVodList on imgVodList.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx,
                                 count(*)                                as likeCount,
                                 json_arrayagg(LikeCategory.likeIconUrl) as likeImgList
                          from Posts
                                   left outer join PostLike on Posts.postIdx = PostLike.postIdx
                                   left outer join LikeCategory on LikeCategory.likeIdx = PostLike.postLikeIdx
                          group by postIdx) as PostLikeCount on PostLikeCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as commentCount
                          from Posts
                                   left outer join PostComment on Posts.postIdx = PostComment.postIdx
                          group by Posts.postIdx) as PostCommentCount on PostCommentCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as sharedCount
                          from Posts
                                   left outer join PostShared on Posts.postIdx = PostShared.postIdx
                          group by Posts.postIdx) as PostSharedCount on PostSharedCount.postIdx = Posts.postIdx
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
  and if(postPrivacyBounds = 'F', (select bit_or(Posts.writerIdx = friendIdx) from Friends where userIdx = 1 group by Friends.userIdx),true)
  and if(? = 'Y', if(isnull(?),true, date(Posts.createAt) = ?) and if(isnull(?),true,(case when ? = 'G' then true when ? = 'M' then $userIdx = Posts.writerIdx else not $userIdx = Posts.writerIdx end)) ,true)
  and Posts.postType = 'P'
  and if($searchIdx = 0,Posts.userIdx = $userIdx or Posts.writerIdx = $userIdx,Posts.writerIdx = $searchIdx or Posts.userIdx = $searchIdx)
  and Posts.isDeleted = 'N'
order by Posts.createAt desc
limit $page,$limit;";

    $st = $pdo->prepare($query);
    $st->execute([$isFilter,$date,$date,$writerType,$writerType,$writerType]);

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

function isValidPostIdx($idx){
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from Posts where postIdx = ? and isDeleted = 'N') as exist";

    $st = $pdo->prepare($query);
    $st->execute([$idx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function getOnePost($postIdx,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select Posts.postType,
       UserName.userIdx                                              as userIdx,
       UserName.name                                                 as userName,
       WriterName.writerIdx                                            as writerIdx,
       WriterName.name                                               as writerName,
       case
            when timestampdiff(month , Posts.createAt, now()) > 6 then concat(year(Posts.createAt),'년',month(Posts.createAt),'달',day(Posts.createAt),'일')
           when timestampdiff(day , Posts.createAt, now()) > 30 then concat(timestampdiff(month , Posts.createAt, now()),'달 전')
           when timestampdiff(hour , Posts.createAt, now()) > 24 then concat(timestampdiff(day , Posts.createAt, now()),'일 전')
           when timestampdiff(minute , Posts.createAt, now()) > 60 then concat(timestampdiff(hour , Posts.createAt, now()),'시간 전')
           when timestampdiff(second, Posts.createAt, now()) > 60 then concat(timestampdiff(minute , Posts.createAt, now()),'분 전')
           else concat(timestampdiff(second, Posts.createAt, now()),'초 전')
           end
           as lastTime,
       if(moodActivity = 'M', '기분', '활동')                            as moodActivity,
       Mood.moodName,
       Mood.moodImgUrl,
       Activity.activityName,
       Activity.activityImgUrl,
       Activity.activityContents,
       Posts.postContents,
       Posts.postImgVideoUrl,
       Posts.postImgVideoType,
       Posts.postSharedType                                          as sharedPostType,
       Posts.postSharedIdx,
       UserName.postIdx,
       imgVodList,
       likeCount,
       likeImgList,
       commentCount,
       sharedCount,
       if(UserPostHide.userIdx = $userIdx, 'Y', 'N')                        as isHided,
       if(PostLike.userIdx = $userIdx, 'Y', 'N')                            as isLiked,
       if(UserPostSaved.userIdx = $userIdx, 'Y', 'N')                       as isSaved,
       if(SettingPostNotification.userIdx = $userIdx, 'Y', 'N')             as isNotificated

from Posts
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.userIdx
      from Posts
               left outer join User on User.userIdx = Posts.userIdx) as UserName on Posts.postIdx = UserName.postIdx
         left outer join
     (select postIdx, concat(User.firstName, User.secondName) as name, Posts.writerIdx
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
         left outer join (select json_arrayagg(json_object('imgVodPostIdx',Posts.postIdx,'imgVodUrl', Posts.postImgVideoUrl,'imgVodType',Posts.postImgVideoType,'imgVodContents',
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
                                                           if(PostLike.userIdx = $userIdx, 'Y', 'N')      as isLiked
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
                                                   where Posts.isDeleted = 'N'
                          group by PostImgVideo.postIdx) as imgVodList on imgVodList.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx,
                                 count(*)                                as likeCount,
                                 json_arrayagg(LikeCategory.likeIconUrl) as likeImgList
                          from Posts
                                   left outer join PostLike on Posts.postIdx = PostLike.postIdx
                                   left outer join LikeCategory on LikeCategory.likeIdx = PostLike.postLikeIdx
                          group by postIdx) as PostLikeCount on PostLikeCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as commentCount
                          from Posts
                                   left outer join PostComment on Posts.postIdx = PostComment.postIdx
                          group by Posts.postIdx) as PostCommentCount on PostCommentCount.postIdx = Posts.postIdx
         left outer join (select Posts.postIdx, count(*) as sharedCount
                          from Posts
                                   left outer join PostShared on Posts.postIdx = PostShared.postIdx
                          group by Posts.postIdx) as PostSharedCount on PostSharedCount.postIdx = Posts.postIdx
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
  and if(postPrivacyBounds = 'F', (select bit_or(Posts.writerIdx = friendIdx) from Friends where userIdx = 1 group by Friends.userIdx),true)
  and Posts.postType = 'P'
  and Posts.postIdx = $postIdx
  and Posts.isDeleted = 'N'
order by Posts.createAt desc";
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

    return $res[0];
}

function editPost($postIdx,$feedUserIdx,$userIdx, $postPrivacyBound, $postContents, $moodActivity, $moodIdx, $activityIdx, $activityContents, $imgVodList, $friendExcept, $friendShow){
    $pdo = pdoSqlConnect();
    if (count($imgVodList) > 1) {
        $query = "update Posts set postPrivacyBounds=?,postContents=?,moodActivity=? where postIdx = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$postPrivacyBound, $postContents, $moodActivity,$postIdx]);
    } else if (count($imgVodList) == 1) {
        $query = "update Posts set postPrivacyBounds=?,postContents=?,postImgVideoUrl=?,postImgVideoType=?,moodActivity=? where postIdx = ?;";
        $st = $pdo->prepare($query);
        $st->execute([$postPrivacyBound, $postContents, $imgVodList[0]->imgVodUrl, $imgVodList[0]->imgVodType, $moodActivity,$postIdx]);
    } else {
        $query = "update Posts set postPrivacyBounds=?,postContents=?,moodActivity=? where postIdx=?;";
        $st = $pdo->prepare($query);
        $st->execute([$postPrivacyBound, $postContents, $moodActivity,$postIdx]);
    }


    if ($moodActivity == 'M') {
        $query = "update PostMood set moodIdx=? where postIdx = ? ";

        $st = $pdo->prepare($query);
        $st->execute([$moodIdx, $postIdx]);
    }
    if ($moodActivity == 'A') {
        $query = "update PostActivity set activityIdx=?, activityContents=? where postIdx = ?";

        $st = $pdo->prepare($query);
        $st->execute([$activityIdx, $activityContents, $postIdx]);
    }


    if ($postPrivacyBound == 'E') {
        $query = "delete from PrivacyBoundExcept where idx = ?";

        $st = $pdo->prepare($query);
        $st->execute([$postIdx]);
        foreach ($friendExcept as $key => $item) {
            $query = "insert into PrivacyBoundExcept(idx, userIdx, exceptApplyType) values (?,?,'P')";

            $st = $pdo->prepare($query);
            $st->execute([$postIdx, $item]);
        }
    }
    if ($postPrivacyBound == 'S') {
        $query = "delete from PrivacyBoundShow where idx = ?";

        $st = $pdo->prepare($query);
        $st->execute([$postIdx]);
        foreach ($friendShow as $key => $item) {
            $query = "insert into PrivacyBoundShow(idx, userIdx, showApplyType) values (?,?,'P')";

            $st = $pdo->prepare($query);
            $st->execute([$postIdx, $item]);
        }
    }
    if(count($imgVodList) > 1) {
        $query = "update PostImgVideo left join Posts on Posts.postIdx=PostImgVideo.imgVideoPostIdx set PostImgVideo.isDeleted = 'Y',Posts.isDeleted = 'Y' where PostImgVideo.postIdx = ?;";

        $st = $pdo->prepare($query);
        $st->execute([$postIdx]);

        foreach ($imgVodList as $key => $item) {
            $query = "insert into Posts(postType,userIdx,writerIdx,postPrivacyBounds,postContents,postImgVideoUrl,postImgVideoType) values ('I',?,?,?,?,?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$feedUserIdx ,$userIdx,$postPrivacyBound, $item->imgVodContents, $item->imgVodUrl, $item->imgVodType]);

            $imgPostIdx = $pdo->lastInsertId();

            $query = "insert into PostImgVideo(postIdx,imgVideoPostIdx) values (?,?)";

            $st = $pdo->prepare($query);
            $st->execute([$postIdx, $imgPostIdx]);
        }
    }
}

function isEditablePost($userIdx,$postIdx){
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from Posts where postIdx = ? and writerIdx = ? and isDeleted = 'N' and (postType = 'P' or postType = 'A')) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function deletePost($postIdx){
    $pdo = pdoSqlConnect();

    $query = "update PostImgVideo left join Posts on Posts.postIdx=PostImgVideo.imgVideoPostIdx set PostImgVideo.isDeleted = 'Y',Posts.isDeleted = 'Y' where PostImgVideo.postIdx = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

    $query = "update Posts set isDeleted = 'Y' where postIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);

}

function isUserLikedPost($userIdx, $postIdx)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostLike WHERE userIdx = ? and postIdx = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]["exist"]);
}

function modifyPostLike($postIdx, $userIdx, $likeIdx, $isLike)
{
    $pdo = pdoSqlConnect();

    if ($isLike == 'N') {
        $query = "update PostLike set postLikeIdx = ?, isDeleted = 'N' where postIdx = ? and userIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$likeIdx, $postIdx, $userIdx]);
    } else {
        $query = "update PostLike set isDeleted = 'Y' where postIdx = ? and userIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$postIdx, $userIdx]);
    }

    $st = null;
    $pdo = null;
}

function makePostLike($postIdx, $userIdx, $likeIdx)
{
    $pdo = pdoSqlConnect();

    $query = "insert into PostLike (postIdx,userIdx,postLikeIdx) values (?,?,?)";
    $st = $pdo->prepare($query);
    $st->execute([$postIdx, $userIdx, $likeIdx]);
    $st = null;
    $pdo = null;
}

function getPostLikeStatus($postIdx,$userIdx){
    $pdo = pdoSqlConnect();
    $query = "SELECT isDeleted from PostLike where userIdx = ? and postIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx, $postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    if($res[0]['isDeleted'] == 'N'){
        return 'Y';
    }else{
        return 'N';
    }
}

function getPostLikeList($postIdx){
    $pdo = pdoSqlConnect();

    $query = "select likeIdx,count(*) as likeCount from PostLike left outer join LikeCategory on postLikeIdx = likeIdx where postIdx = ? and isDeleted = 'N' group by likeIdx;";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getPostLikeUserList($postIdx,$userIdx,$page,$limit,$likeFilter){
    $pdo = pdoSqlConnect();

    $page = ($page - 1) * $limit;

    if($likeFilter == 1){
        $query = "select userIdx,likeIdx,userProfileImgUrl,userName,count(if(isKnowingFriend = 1,isKnowingFriend,null)) as knowingFriendCount,bit_or(if(friend = $userIdx,true,false)) as isFriend
from (select PostLike.userIdx, likeIdx, profileImgUrl as userProfileImgUrl, concat(firstName, secondName) as userName, f1.friendIdx as friend, (select bit_or(friendIdx = f2.friendIdx) from Friends where Friends.userIdx = 1) as isKnowingFriend
from PostLike
         left outer join LikeCategory on postLikeIdx = likeIdx
         left outer join User on User.userIdx = PostLike.userIdx
         left outer join Friends as f1 on f1.userIdx = PostLike.userIdx
         left outer join Friends as f2 on f1.friendIdx = f2.userIdx
where postIdx = $postIdx
  and PostLike.isDeleted = 'N') as F
group by userIdx
limit $page, $limit;";
    }else{
        $likeFilter = $likeFilter - 1;
        $query = "select userIdx,likeIdx,userProfileImgUrl,userName,count(if(isKnowingFriend = 1,isKnowingFriend,null)) as knowingFriendCount,bit_or(if(friend = $userIdx,true,false)) as isFriend
from (select PostLike.userIdx, likeIdx, profileImgUrl as userProfileImgUrl, concat(firstName, secondName) as userName, f1.friendIdx as friend, (select bit_or(friendIdx = f2.friendIdx) from Friends where Friends.userIdx = 1) as isKnowingFriend
from PostLike
         left outer join LikeCategory on postLikeIdx = likeIdx
         left outer join User on User.userIdx = PostLike.userIdx
         left outer join Friends as f1 on f1.userIdx = PostLike.userIdx
         left outer join Friends as f2 on f1.friendIdx = f2.userIdx
where postIdx = $postIdx
  and PostLike.isDeleted = 'N') as F
  where likeIdx = $likeFilter
group by userIdx
limit $page, $limit;";
    }
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function isPostHided($postIdx,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from UserPostHide where postIdx = ? and userIdx = ?) as exist";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];

}

function getPostHided($postIdx,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "select isDeleted from UserPostHide where postIdx = ? and userIdx = ?";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['isDeleted'] == 'N' ? 'Y' : 'N';
}

function makePostHide($postIdx,$userIdx){
    $pdo = pdoSqlConnect();

    $query = "insert into UserPostHide(postIdx,userIdx) values (?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$postIdx,$userIdx]);
}

function modifyPostHide($postIdx,$userIdx,$isHided){
    $pdo = pdoSqlConnect();

    if($isHided == 'N'){
        $query = "update UserPostHide set isDeleted = 'Y' where userIdx = ? and postIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$postIdx]);
    }else{
        $query = "update UserPostHide set isDeleted = 'N' where userIdx = ? and postIdx = ?";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$postIdx]);
    }
}