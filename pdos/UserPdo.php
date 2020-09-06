<?php

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

function getUserProfileInfo($userIdx,$profileUserIdx){
    $pdo = pdoSqlConnect();

    $query = "select profileImgUrl,
       backImgUrl,
       concat(firstName, secondName) as userName,
       UserCareerList.careerList,
       UserUnivList.univList,
       UserHighList.highschoolList,
       ResidencePlace.residenceName,
       OriginPlace.originPlaceName,
       SocialLink.snsList,
       Website.websiteList
from User
         left outer join (select userIdx, json_arrayagg(json_object('careerName', careerName)) as careerList
                          from UserCareer
                          where case
                                    when UserCareer.careerPrivacyBounds = 'F' then true =
                                                                                   (select bit_or(if($userIdx = Friends.friendIdx, true, false))
                                                                                    from Friends
                                                                                    where Friends.userIdx = $profileUserIdx
                                                                                    group by Friends.userIdx) or $profileUserIdx = $userIdx
                                    when UserCareer.careerPrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                    else true
                                    end
                          group by userIdx) as UserCareerList on User.userIdx = UserCareerList.userIdx
         left outer join (select userIdx, json_arrayagg(json_object('univName', universityName)) as univList
                          from UserUniversity
                          where case
                                    when UserUniversity.universityPrivacyBounds = 'F' then true =
                                                                                   (select bit_or(if($userIdx = Friends.friendIdx, true, false))
                                                                                    from Friends
                                                                                    where Friends.userIdx = $profileUserIdx
                                                                                    group by Friends.userIdx) or $profileUserIdx = $userIdx
                                    when UserUniversity.universityPrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                    else true
                                    end
                          group by userIdx) as UserUnivList on User.userIdx = UserUnivList.userIdx
         left outer join (select userIdx,
                                 json_arrayagg(json_object('highschooolName', highschoolName)) as highschoolList
                          from UserHighschool
                          where case
                                    when UserHighschool.highschoolPrivacyBounds = 'F' then true =
                                                                                   (select bit_or(if($userIdx = Friends.friendIdx, true, false))
                                                                                    from Friends
                                                                                    where Friends.userIdx = $profileUserIdx
                                                                                    group by Friends.userIdx) or $profileUserIdx = $userIdx
                                    when UserHighschool.highschoolPrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                    else true
                                    end
                          group by userIdx) as UserHighList on User.userIdx = UserHighList.userIdx
         left outer join (select * from UserResidencePlace where case when UserResidencePlace.residencePrivacyBounds = 'F' then true = (select bit_or(if($userIdx = Friends.friendIdx, true, false)) or $profileUserIdx = $userIdx from Friends where Friends.userIdx = $profileUserIdx group by Friends.userIdx)
                                     when UserResidencePlace.residencePrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                     else true
                                end) as ResidencePlace on ResidencePlace.userIdx = User.userIdx
         left outer join (select * from UserOriginPlace where case when originPlacePrivacyBounds = 'F' then true = (select bit_or(if($userIdx = Friends.friendIdx, true, false)) or $profileUserIdx = $userIdx from Friends where Friends.userIdx = $profileUserIdx group by Friends.userIdx)
                                     when originPlacePrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                     else true
                                end) as OriginPlace on OriginPlace.userIdx = User.userIdx
         left outer join (select userIdx, json_arrayagg(json_object('snsIdx',snsCategoryIdx,'snsIdName',socialLinkName)) as snsList from UserSocialLink where case when UserSocialLink.socialLinkPrivacyBounds = 'F' then true = (select bit_or(if($userIdx = Friends.friendIdx, true, false)) or $profileUserIdx = $userIdx from Friends where Friends.userIdx = $profileUserIdx group by Friends.userIdx)
                                     when UserSocialLink.socialLinkPrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                     else true
                                end) as SocialLink on SocialLink.userIdx = User.userIdx
         left outer join (select userIdx, json_arrayagg(json_object('websiteUrl',websiteUrl)) as websiteList from UserWebsite where case when UserWebsite.websitePrivacyBounds = 'F' then true = (select bit_or(if($userIdx = Friends.friendIdx, true, false)) or $profileUserIdx = $userIdx from Friends where Friends.userIdx = $profileUserIdx group by Friends.userIdx)
                                     when UserWebsite.websitePrivacyBounds = 'M' then $userIdx = $profileUserIdx
                                     else true
                                end) as Website on Website.userIdx = User.userIdx
                                where User.isDeleted = 'N'";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $res[0]['careerList'] = json_decode($res[0]['careerList']);
    $res[0]['univList'] = json_decode($res[0]['univList']);
    $res[0]['highschoolList'] = json_decode($res[0]['highschoolList']);
    $res[0]['snsList'] = json_decode($res[0]['snsList']);
    $res[0]['websiteList'] = json_decode($res[0]['websiteList']);

    $st = null;
    $pdo = null;

    return $res[0];
}

function isUserBlocked($userIdx,$profileUserIdx){
    $pdo = pdoSqlConnect();

    $query = "select exists(select * from Blocked where userIdx = ? and blockedUserIdx = ?) as exist ";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx,$profileUserIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}

function getProfileFriendInfo($userIdx,$profileUserIdx){
    $pdo = pdoSqlConnect();

    $query = "select (select count(userIdx)
        from Friends
        where Friends.userIdx = $profileUserIdx
          and Friends.isDeleted = 'N'
          and friendIdx not in (select blockedUserIdx
                                from Blocked
                                where (Blocked.userIdx = $userIdx and Blocked.isDeleted = 'N')
                                   or (Blocked.userIdx = $profileUserIdx and Blocked.isDeleted = 'N'))) as friendCount,
       (select count(*)
        from (select * from Friends where userIdx = $userIdx and isDeleted = 'N') as UserFriend
                 inner join (select * from Friends where userIdx = $profileUserIdx and isDeleted = 'N') as ProfileUserFriend
                            on UserFriend.friendIdx = ProfileUserFriend.friendIdx
        where ProfileUserFriend.friendIdx not in
              (select blockedUserIdx from Blocked where Blocked.userIdx = $userIdx))            as totalKnowingFriendCount,
       (select json_arrayagg(json_object('friendImg', User.profileImgUrl, 'friendName',
                                 concat(User.firstName, User.secondName), 'knowingFriendCount',
                                 if(isnull(KnowingCount.count),0,KnowingCount.count),'idx',ProfileUserFriend.friendIdx
    ))
from (select * from Friends where userIdx = $profileUserIdx) as ProfileUserFriend
         left outer join User on ProfileUserFriend.friendIdx = User.userIdx
         left outer join (select count(Friends.friendIdx) as count, UserFriend.friendIdx
                          from (select * from Friends where userIdx = $profileUserIdx and isDeleted = 'N') as UserFriend
                                   left outer join Friends on UserFriend.friendIdx = Friends.userIdx
                          where Friends.friendIdx in
                                (select friendIdx from Friends where userIdx = $userIdx and isDeleted = 'N')
                          group by UserFriend.friendIdx) as KnowingCount on KnowingCount.friendIdx = ProfileUserFriend.friendIdx
where ProfileUserFriend.isDeleted = 'N'
  and User.isDeleted = 'N'
  and ProfileUserFriend.friendIdx not in (select blockedUserIdx
                                          from Blocked
                                          where Blocked.userIdx = $profileUserIdx and Blocked.userIdx = $userIdx
                                            and Blocked.isDeleted = 'N')
group by ProfileUserFriend.userIdx) as friendList;";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $res[0]['friendList'] = json_decode($res[0]['friendList']);

    $st = null;
    $pdo = null;

    return $res[0];
}