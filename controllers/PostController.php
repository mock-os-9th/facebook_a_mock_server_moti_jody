<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            http_response_code(200);
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        case "commentLikePush":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);


            if ($userIdx == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $commentIdx = intval($vars["idx"]);
            $isLike = $req->isLike;
            $likeIdx = $req->likeIdx;

            if (gettype($commentIdx) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (gettype($isLike) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($likeIdx) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (strlen($isLike) != 1) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "좋아요 여부 길이 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($isLike != 'N' && $isLike != 'Y') {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "좋아요 여부 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 인덱스가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($isLike)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "좋아요 여부가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($likeIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "좋아요 인덱스가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isValidCommentIdx($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isUserLikedComment($userIdx, $commentIdx)) {
                modifyCommentLike($commentIdx, $userIdx, $likeIdx, $isLike);
            } else {
                makeCommentLike($commentIdx, $userIdx, $likeIdx);
            }

            $res->isSuccess = true;
            $res->code = 200;
            $res->message = "좋아요 변경 완료";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            return;

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getMainFeed":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $page = $_GET["page"];
            $page = isset($page) ? intval($page) : null;
            $limit = $_GET["limit"];
            $limit = isset($limit) ? intval($limit) : null;


            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            if ($userIdx == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($page) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($limit) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($page < 1){
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "page는 1부터 시작입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $res->page = $page;
            $res->limit = $limit;
            $res->result = getMainFeed($page, $limit, $userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "메인 피드 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createPost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $feedUserIdx = isset($req->userIdx) ? $req->feedUserIdx : null;
            $postPrivacyBound = isset($req->postPrivacyBound) ? $req->postPrivacyBound : null;
            $postContents = isset($req->postContents) ? $req->postContents : null;
            $moodActivity = isset($req->moodActivity) ? $req->moodActivity : null;
            $moodIdx = isset($req->moodIdx) ? $req->moodIdx : null;
            $activityIdx = isset($req->activityIdx) ? $req->activityIdx : null;
            $activityContents = isset($req->activityContents) ? $req->activityContents : null;
            $imgVodList = isset($req->imgVodList) ? $req->imgVodList : null;
            $friendExcept = isset($req->friendExcept) ? $req->friendExcept : null;
            $friendShow = isset($req->friendShow) ? $req->friendShow : null;


            if (is_null($postPrivacyBound)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "공개범위는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($postContents) && is_null($imgVodList)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "본문 혹은 사진은 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($moodActivity == 'M') {
                if (is_null($moodIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 442;
                    $res->message = "moodActivity가 M이면 moodIdx는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($moodIdx) != 'integer') {
                    $res->isSuccess = FALSE;
                    $res->code = 417;
                    $res->message = "moodIdx의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!isValidMoodIdx($moodIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 453;
                    $res->message = "존재하지 않는 moodIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

            }
            if ($moodActivity == 'A') {
                if (is_null($activityIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 442;
                    $res->message = "moodActivity가 A이면 activityIdx는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if(is_null($activityContents)){
                    $res->isSuccess = FALSE;
                    $res->code = 447;
                    $res->message = "moodActivity가 A이면 activityContents는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($activityIdx) != 'integer') {
                    $res->isSuccess = FALSE;
                    $res->code = 418;
                    $res->message = "activityIdx의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                if (!isValidActivityIdx($activityIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 454;
                    $res->message = "존재하지 않는 activityIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if ($postPrivacyBound == 'E') {
                if (is_null($friendExcept)) {
                    $res->isSuccess = FALSE;
                    $res->code = 444;
                    $res->message = "postPrivacyBound가 E이면 friendExcept는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($friendExcept) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 416;
                    $res->message = "제외할 친구리스트 타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                foreach ($friendExcept as $key => $item) {
                    if (gettype($item) != 'integer') {
                        $res->isSuccess = FALSE;
                        $res->code = 492;
                        $res->message = "제외할 친구 인덱스 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($friendExcept as $key => $item) {
                    if (!isValidUserIdx($item)) {
                        $res->isSuccess = FALSE;
                        $res->code = 452;
                        $res->message = "존재하지 않는 userIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if(isFriend($userIdx,$item) == 0){
                        $res->isSuccess = FALSE;
                        $res->code = 457;
                        $res->message = "friendExcept 친구가 아닌 인덱스가 있습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }
            }
            if ($postPrivacyBound == 'S') {
                if (is_null($friendShow)) {
                    $res->isSuccess = FALSE;
                    $res->code = 445;
                    $res->message = "postPrivacyBound가 S이면 friendShow는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($friendShow) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 417;
                    $res->message = "보여줄 친구리스트 타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                foreach ($friendShow as $key => $item) {
                    if (gettype($item) != 'integer') {
                        $res->isSuccess = FALSE;
                        $res->code = 493;
                        $res->message = "보여줄 친구 인덱스 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($friendShow as $key => $item) {
                    if (!isValidUserIdx($item)) {
                        $res->isSuccess = FALSE;
                        $res->code = 453;
                        $res->message = "존재하지 않는 userIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if(isFriend($userIdx,$item) == 0){
                        $res->isSuccess = FALSE;
                        $res->code = 456;
                        $res->message = "friendShow 친구가 아닌 인덱스가 있습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }
            }

            if (!is_null($feedUserIdx)) {
                if (gettype($feedUserIdx) != 'integer') {
                    $res->isSuccess = FALSE;
                    $res->code = 410;
                    $res->message = "feedUserIdx의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!isValidUserIdx($feedUserIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 451;
                    $res->message = "존재하지 않는 userIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }
            if (gettype($postPrivacyBound) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "postPrivacyBound의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_null($postContents)) {
                if (gettype($postContents) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 411;
                    $res->message = "postContents의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (strlen($postContents) > 500) {
                    $res->isSuccess = FALSE;
                    $res->code = 421;
                    $res->message = "본문 길이 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if (!is_null($moodActivity)) {
                if (gettype($moodActivity) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 412;
                    $res->message = "moodActivity의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (strlen($moodActivity) != 1) {
                    $res->isSuccess = FALSE;
                    $res->code = 422;
                    $res->message = "moodActivity 길이 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (isValidMoodActivityType($moodActivity) == 0) {
                    $res->isSuccess = FALSE;
                    $res->code = 431;
                    $res->message = "moodActivity 유형 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }


            if (!is_null($activityContents) && gettype($activityContents) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 419;
                $res->message = "activityContents의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if (!is_null($imgVodList)) {
                if (gettype($imgVodList) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 490;
                    $res->message = "imgVodList의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                foreach ($imgVodList as $key => $item) {
                    if (gettype($item->imgVodUrl) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 413;
                        $res->message = "imgVodUrl의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (!is_null($item->imgVodList)&&gettype($item->imgVodContents) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 414;
                        $res->message = "imgVodContents의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (gettype($item->imgVodType) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 494;
                        $res->message = "imgVodType의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($imgVodList as $key => $item) {
                    if (!is_null($item->imgVodList)&&strlen($item->imgVodContents) > 100) {
                        $res->isSuccess = FALSE;
                        $res->code = 423;
                        $res->message = "imgVodContents의 길이 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (strlen($item->imgVodType) > 100) {
                        $res->isSuccess = FALSE;
                        $res->code = 424;
                        $res->message = "imgVodType의 길이 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (!is_null($item->imgVodUrl) && is_null($item->imgVodType)) {
                        $res->isSuccess = FALSE;
                        $res->code = 446;
                        $res->message = "imgVodUrl이 들어가면 imgVodType은 필수입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }
            }

            if (isValidPrivacyBoundType($postPrivacyBound) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "postPrivacyBound 유형 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (strlen($postPrivacyBound) != 1) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "공개범위 길이 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            createPost($userIdx, $feedUserIdx, $postPrivacyBound, $postContents, $moodActivity, $moodIdx, $activityIdx, $activityContents, $imgVodList, $friendExcept, $friendShow);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "게시글 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getPersonalFeed":
            http_response_code(200);
            $page = $_GET["page"];
            $page = isset($page) ? intval($page) : null;
            $limit = $_GET["limit"];
            $limit = isset($limit) ? intval($limit) : null;
            $isFilter = $req->isFilter;
            $isFilter = isset($isFilter) ? $isFilter : null;
            $date = $req->date;
            $date = isset($date) ? $date : null;
            $writerType = $req->writerType;
            $writerType = isset($writerType) ? $writerType : null;
            $searchIdx = $vars['idx'];
            $searchIdx = isset($searchIdx) ? intval($searchIdx) : null;

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            if ($userIdx == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($searchIdx != 0 && isValidUserIdx($searchIdx) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "userIdx에 해당하는 유저가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($page) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($limit) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (gettype($searchIdx) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "유저 인덱스의 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (gettype($isFilter) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "isFilter 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isValidYNType($isFilter) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "isFilter 유형 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($searchIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "searchIdx is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($isFilter)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "isFilter is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "page is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 443;
                $res->message = "limit is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($isFilter == 'Y'){
                if (is_null($date)&&is_null($writerType)) {
                    $res->isSuccess = FALSE;
                    $res->code = 444;
                    $res->message = "isFilter가 Y이면 date나 writerType 둘 중 하나는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!is_null($date)&&gettype($date) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 414;
                    $res->message = "date 타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                if (!is_null($writerType)&&gettype($writerType) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 415;
                    $res->message = "writerType 유형 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!is_null($writerType)&&isValidWriterType($writerType) == 0) {
                    $res->isSuccess = FALSE;
                    $res->code = 431;
                    $res->message = "writerType 유형 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                if (!is_null($date)&&isValidDate($date) == 0) {
                    $res->isSuccess = FALSE;
                    $res->code = 432;
                    $res->message = "date 유형 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }
            if($page < 1){
                $res->isSuccess = FALSE;
                $res->code = 433;
                $res->message = "page는 1부터 시작입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->page = $page;
            $res->limit = $limit;
            $res->result = getPersonalFeed($page, $limit,$isFilter,$date,$writerType, $userIdx, $searchIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "개인 피드 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getOnePost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $postIdx = $vars['idx'];
            $postIdx = isset($postIdx) ? intval($postIdx) : null;

            if(isValidPostIdx($postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "해당 인덱스의 게시물이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getOnePost($postIdx,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "게시물 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "editPost":
            http_response_code(200);

            $feedUserIdx = isset($req->userIdx) ? $req->feedUserIdx : null;
            $postPrivacyBound = isset($req->postPrivacyBound) ? $req->postPrivacyBound : null;
            $postContents = isset($req->postContents) ? $req->postContents : null;
            $moodActivity = isset($req->moodActivity) ? $req->moodActivity : null;
            $moodIdx = isset($req->moodIdx) ? $req->moodIdx : null;
            $activityIdx = isset($req->activityIdx) ? $req->activityIdx : null;
            $activityContents = isset($req->activityContents) ? $req->activityContents : null;
            $imgVodList = isset($req->imgVodList) ? $req->imgVodList : null;
            $friendExcept = isset($req->friendExcept) ? $req->friendExcept : null;
            $friendShow = isset($req->friendShow) ? $req->friendShow : null;
            $postIdx = isset($vars["idx"])?intval($vars["idx"]):null;

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            if(isEditablePost($userIdx,$postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "수정 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_null($postPrivacyBound)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "공개범위는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($postContents) && is_null($imgVodList)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "본문 혹은 사진은 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(isValidPostIdx($postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 455;
                $res->message = "존재하지 않는 게시글 인덱스입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($moodActivity == 'M') {
                if (is_null($moodIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 442;
                    $res->message = "moodActivity가 M이면 moodIdx는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($moodIdx) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 418;
                    $res->message = "moodIdx의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!isValidMoodIdx($moodIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 454;
                    $res->message = "존재하지 않는 moodIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

            }
            if ($moodActivity == 'A') {
                if (is_null($activityIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 442;
                    $res->message = "moodActivity가 A이면 activityIdx는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if(is_null($activityContents)){
                    $res->isSuccess = FALSE;
                    $res->code = 447;
                    $res->message = "moodActivity가 A이면 activityContents는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($activityIdx) != 'integer') {
                    $res->isSuccess = FALSE;
                    $res->code = 419;
                    $res->message = "activityIdx의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                if (!isValidActivityIdx($activityIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 455;
                    $res->message = "존재하지 않는 activityIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if ($postPrivacyBound == 'E') {
                if (is_null($friendExcept)) {
                    $res->isSuccess = FALSE;
                    $res->code = 444;
                    $res->message = "postPrivacyBound가 E이면 friendExcept는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($friendExcept) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 415;
                    $res->message = "제외할 친구리스트 타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                foreach ($friendExcept as $key => $item) {
                    if (gettype($item) != 'integer') {
                        $res->isSuccess = FALSE;
                        $res->code = 491;
                        $res->message = "제외할 친구 인덱스 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($friendExcept as $key => $item) {
                    if (!isValidUserIdx($item)) {
                        $res->isSuccess = FALSE;
                        $res->code = 451;
                        $res->message = "존재하지 않는 userIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if(isFriend($userIdx,$item) == 0){
                        $res->isSuccess = FALSE;
                        $res->code = 458;
                        $res->message = "friendExcept 친구가 아닌 인덱스가 있습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

            }
            if ($postPrivacyBound == 'S') {
                if (is_null($friendShow)) {
                    $res->isSuccess = FALSE;
                    $res->code = 445;
                    $res->message = "postPrivacyBound가 S이면 friendShow는 필수입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (gettype($friendShow) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 416;
                    $res->message = "보여줄 친구리스트 타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }


                foreach ($friendShow as $key => $item) {
                    if (gettype($item) != 'integer') {
                        $res->isSuccess = FALSE;
                        $res->code = 492;
                        $res->message = "보여줄 친구 인덱스 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($friendShow as $key => $item) {
                    if (!isValidUserIdx($item)) {
                        $res->isSuccess = FALSE;
                        $res->code = 452;
                        $res->message = "존재하지 않는 userIdx입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if(isFriend($userIdx,$item) == 0){
                        $res->isSuccess = FALSE;
                        $res->code = 457;
                        $res->message = "friendShow 친구가 아닌 인덱스가 있습니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }
            }
            if (gettype($postPrivacyBound) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "postPrivacyBound의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_null($postContents)) {
                if (gettype($postContents) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 412;
                    $res->message = "postContents의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (strlen($postContents) > 500) {
                    $res->isSuccess = FALSE;
                    $res->code = 421;
                    $res->message = "본문 길이 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if (!is_null($moodActivity)) {
                if (gettype($moodActivity) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 413;
                    $res->message = "moodActivity의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (strlen($moodActivity) != 1) {
                    $res->isSuccess = FALSE;
                    $res->code = 422;
                    $res->message = "moodActivity 길이 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (isValidMoodActivityType($moodActivity) == 0) {
                    $res->isSuccess = FALSE;
                    $res->code = 431;
                    $res->message = "moodActivity 유형 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }


            if (!is_null($activityContents) && gettype($activityContents) != 'string') {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "activityContents의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if (!is_null($imgVodList)) {
                if (gettype($imgVodList) != 'array') {
                    $res->isSuccess = FALSE;
                    $res->code = 491;
                    $res->message = "imgVodList의 타입이 잘못됐습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }

                foreach ($imgVodList as $key => $item) {
                    if (gettype($item->imgVodUrl) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 414;
                        $res->message = "imgVodUrl의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (!is_null($item->imgVodList)&&gettype($item->imgVodContents) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 415;
                        $res->message = "imgVodContents의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (gettype($item->imgVodType) != 'string') {
                        $res->isSuccess = FALSE;
                        $res->code = 493;
                        $res->message = "imgVodType의 타입 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }

                foreach ($imgVodList as $key => $item) {
                    if (!is_null($item->imgVodList)&&strlen($item->imgVodContents) > 100) {
                        $res->isSuccess = FALSE;
                        $res->code = 423;
                        $res->message = "imgVodContents의 길이 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (strlen($item->imgVodType) > 100) {
                        $res->isSuccess = FALSE;
                        $res->code = 424;
                        $res->message = "imgVodType의 길이 오류";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                    if (!is_null($item->imgVodUrl) && is_null($item->imgVodType)) {
                        $res->isSuccess = FALSE;
                        $res->code = 446;
                        $res->message = "imgVodUrl이 들어가면 imgVodType은 필수입니다";
                        echo json_encode($res, JSON_NUMERIC_CHECK);
                        addErrorLogs($errorLogs, $res, $req);
                        return;
                    }
                }
            }
            if (!is_null($feedUserIdx)) {
                if (gettype($feedUserIdx) != 'string') {
                    $res->isSuccess = FALSE;
                    $res->code = 494;
                    $res->message = "feedUserIdx타입 오류";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
                if (!isValidUserIdx($feedUserIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 451;
                    $res->message = "존재하지 않는 feedUserIdx입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if (isValidPrivacyBoundType($postPrivacyBound) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "postPrivacyBound 유형 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (strlen($postPrivacyBound) != 1) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "공개범위 길이 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            editPost($postIdx,$feedUserIdx,$userIdx, $postPrivacyBound, $postContents, $moodActivity, $moodIdx, $activityIdx, $activityContents, $imgVodList, $friendExcept, $friendShow);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "게시글 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deletePost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $postIdx = $vars["idx"];
            $postIdx = isset($postIdx)?intval($postIdx):null;

            if(isValidPostIdx($postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시물입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isEditablePost($userIdx,$postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "삭제할 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            deletePost($postIdx);
            $res->postIdx = $postIdx;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "게시글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "postLikePush":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];


            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $postIdx = intval($vars["idx"]);
            $likeIdx = $req->likeIdx;

            if (gettype($postIdx) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "게시글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($likeIdx) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "likeIdx 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "게시글 인덱스는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($likeIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "likeIdx는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isValidPostIdx($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $isLike = null;

            if (isUserLikedPost($userIdx, $postIdx)) {
                $isLike = getPostLikeStatus($postIdx,$userIdx);
                modifyPostLike($postIdx, $userIdx, $likeIdx,$isLike);
            } else {
                makePostLike($postIdx, $userIdx, $likeIdx);
            }

            if($isLike == 'N'){
                $res->isLike = 'Y';
            }else{
                $res->isLike = 'N';
            }

            $res->isSuccess = true;
            $res->code = 200;
            $res->message = "좋아요 변경 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getPostLikeList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $page = $_GET["page"];
            $page = isset($page)?intval($page):null;
            $limit = $_GET["limit"];
            $limit = isset($limit)?intval($limit):null;
            $likeFilter = $_GET["likeFilter"];
            $likeFilter = isset($likeFilter)?intval($likeFilter):null;
            $postIdx = $vars["idx"];
            $postIdx = isset($postIdx)?intval($postIdx):null;

            if($postIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "게시물 인덱스의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($page == 0){
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "page의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($limit == 0){
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "limit의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($likeFilter == 0){
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "likeFilter의 타입이 잘못됐습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(isValidlikeFilter($likeFilter) == 0){
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "likeFilter유형 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($postIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "게시글 idx는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($page)){
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "page는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($limit)){
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "limit는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($likeFilter)){
                $res->isSuccess = FALSE;
                $res->code = 443;
                $res->message = "likeFilter는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(isValidPostIdx($postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시물입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->likeList = getPostLikeList($postIdx);
            $res->userList = getPostLikeUserList($postIdx,$userIdx,$page,$limit,$likeFilter);
            $res->page = $page;
            $res->limit = $limit;
            $res->isSuccess = true;
            $res->code = 200;
            $res->message = "좋아요 리스트 조회 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "hidePost":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (isValidHeader($jwt, JWT_SECRET_KEY) == 0) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $userIdx = getUserIdxFromJwt($jwt, JWT_SECRET_KEY);

            $postIdx = $vars["idx"];
            $postIdx = isset($postIdx)?intval($postIdx):null;

            if($postIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "게시글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($postIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "게시글 인덱스는 필수입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(isValidPostIdx($postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시글 인덱스 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(isEditablePost($userIdx,$postIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "숨기기 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isPostHided($postIdx) == 0){
                makePostHide($postIdx,$userIdx);
            }else{
                $isHided = getPostHided($postIdx,$userIdx);
                modifyPostHide($postIdx,$userIdx,$isHided);
            }

            $res->isHided = $isHided = 'N' ? 'Y' : 'N';
            $res->isSuccess = true;
            $res->code = 200;
            $res->message = "게시글 숨기기 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
