<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
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

        case "requestFriend" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 친구인 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isRequestedFriend($idx, $targetIdx) || isRequestedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 461;
                $res->message = "이미 친구 요청을 했거나 요청을 받은 친구 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "차단 된 친구는 친구를 요청 할 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isAcceptedOrDeletedBefore($idx, $targetIdx)) {
                updateRequestFriend($idx, $targetIdx);
            }
            else {
                requestFriend($idx, $targetIdx);
            }
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 요청 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "acceptFriendRequest" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 친구 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 462;
                $res->message = "차단 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isRequestedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "들어온 친구 요청이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isRequestedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 461;
                $res->message = "사용자가 요청을 보낸 친구 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isDeletedFriend($idx, $targetIdx) || isDeletedFriend($targetIdx, $idx)) {
                acceptFriendRequest($targetIdx, $idx);
                unDeleteFriend($idx, $targetIdx);
                unDeleteFriend($targetIdx, $idx);
                followUser($idx, $targetIdx);
                followUser($targetIdx, $idx);
            }
            else {
                acceptFriendRequest($targetIdx, $idx);
                addFriend($idx, $targetIdx);
                addFriend($targetIdx, $idx);
                addFollowing($idx, $targetIdx);
                addFollowing($targetIdx, $idx);
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 요청 수락 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "rejectFriendRequest" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isAcceptedOrDeletedBefore($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 친구 요청을 거절한 친구 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 462;
                $res->message = "차단 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isRequestedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "들어온 친구 요청이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isRequestedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 461;
                $res->message = "사용자가 요청을 보낸 친구 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            rejectFriendRequest($targetIdx, $idx);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 요청 거절 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserFriendList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == 0) {
                $targetIdx = $idx;
            }

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!friendExist($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "조회할 친구가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isBlockedFriend($idx, $targetIdx) || isBlockedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getUserFriendList($idx, $targetIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "전체 친구 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "blockUser" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 차단 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "친구가 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            blockUser($idx, $targetIdx);

            if (isDeletedFriend($idx, $targetIdx) || isDeletedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 461;
                $res->message = "이미 삭제 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            deleteFriend($idx, $targetIdx);
            deleteFriend($targetIdx, $idx);

            if (isUnFollowedFriend($idx, $targetIdx) || isUnFollowedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 462;
                $res->message = "이미 언팔로우 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            unfollowUser($idx, $targetIdx);
            unfollowUser($targetIdx, $idx);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 차단 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "followUser" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 471;
                $res->message = "차단 된 친구는 팔로우 할 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isFollowedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 팔로우 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "친구가 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            followUser($idx, $targetIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 팔로우 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "unfollowUser" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isBlockedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 471;
                $res->message = "차단 된 친구는 언팔로우 할 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isUnFollowedFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 언팔로우 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isFriend($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "친구가 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            unfollowUser($idx, $targetIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 언팔로우 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteFriend" :
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (isBlockedFriend($idx, $targetIdx) || isBlockedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 471;
                $res->message = "차단 된 친구는 친구를 끊을 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isDeletedFriend($idx, $targetIdx) || isDeletedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 삭제 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isFriend($idx, $targetIdx) || !isFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "친구가 아닙니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            deleteFriend($idx, $targetIdx);
            deleteFriend($targetIdx, $idx);

            if (isUnFollowedFriend($idx, $targetIdx) || isUnFollowedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 461;
                $res->message = "이미 언팔로우 된 사용자 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            unfollowUser($idx, $targetIdx);
            unfollowUser($targetIdx, $idx);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getKnownFriendList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isBlockedFriend($idx, $targetIdx) || isBlockedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($idx == $targetIdx) {
                $res->isSuccess = FALSE;
                $res->code = 490;
                $res->message = "자기 자신이 idx가 될 수 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!isKnownFriendExist($idx, $targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "조회 할 함께 아는 친구가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getKnownFriendList($idx, $targetIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "함께 아는 친구 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getRequestedFriendList":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            if(!isRequestedFriendExist($idx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "조회 할 친구 요청이 없습니다 ";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getRequestedFriendList($idx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 요청 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "searchFriend":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "해당유저가 존재하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $idx = getUserIdxFromId($data->id);

            $targetIdx = $vars['idx'];
            $targetIdx = isset($targetIdx) ? intval($targetIdx) : null;

            $keyword = isset($_GET['keyword']) ? intval($_GET['keyword']) : null;

            if ($targetIdx == 0) {
                $targetIdx = $idx;
            }

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($keyword == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "keyword가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_string($keyword)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "keyword는 String 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!friendExistWithKeyword($idx, $targetIdx, $keyword)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "친구 검색 결과가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (isBlockedFriend($idx, $targetIdx) || isBlockedFriend($targetIdx, $idx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = searchFriend($idx, $targetIdx, $keyword);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "친구 검색 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
