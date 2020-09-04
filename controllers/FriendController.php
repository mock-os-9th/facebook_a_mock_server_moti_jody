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

            $targetIdx = isset($vars["idx"]) ? $vars["idx"] : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if ($targetIdx == 0) {
                $targetIdx = $idx;
            } else if (!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
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

            $targetIdx = isset($vars["idx"]) ? $vars["idx"] : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_integer($targetIdx)) {
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

            $res->result = blockUser($idx, $targetIdx);
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

            $targetIdx = isset($vars["idx"]) ? $vars["idx"] : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_integer($targetIdx)) {
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

            $targetIdx = isset($vars["idx"]) ? $vars["idx"] : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (is_integer($targetIdx)) {
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
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
