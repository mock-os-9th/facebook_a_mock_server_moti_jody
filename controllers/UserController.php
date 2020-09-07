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
            
        /*
         * API No. 5
         * API Name : 프로필 정보 가져오기 API
         * 마지막 수정 날짜 : 19.04.29
         */

        case "getUserFriend":
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

            if ($targetIdx != $idx) {
                if (!isValidAccessRights($idx, $targetIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 470;
                    $res->message = "접근 권한이 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            $res->result = getUserFriend($targetIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "전체 친구 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserCareer":
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
            $bound = isset($_GET["bound"]) ? $_GET["bound"] : null;

            if ($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($bound == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "bound가 null 입니다";
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
            if (!is_string($bound)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "bound는 String 이여야 합니다";
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


            if (!isCareerIdxExists($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "경력이 존재 하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (strlen($bound) != 1) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "bound의 길이는 1 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if ($targetIdx != $idx) {
                if (!isValidAccessRights($idx, $targetIdx)) {
                    $res->isSuccess = FALSE;
                    $res->code = 470;
                    $res->message = "접근 권한이 없습니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            $res->result = getUserCareer($targetIdx, $bound);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "경력 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfileInfo":
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

            $profileUserIdx = $vars["idx"];
            $profileUserIdx = isset($profileUserIdx)?intval($profileUserIdx):null;

            if(is_null($profileUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isValidUserIdx($profileUserIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($profileUserIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserBlocked($userIdx,$profileUserIdx) == 1){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getUserProfileInfo($userIdx,$profileUserIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "프로필 정보 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfileFriend":
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

            $profileUserIdx = $vars["idx"];
            $profileUserIdx = isset($profileUserIdx)?intval($profileUserIdx):null;

            if(is_null($profileUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isValidUserIdx($profileUserIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($profileUserIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserBlocked($userIdx,$profileUserIdx) == 1){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getProfileFriendInfo($userIdx,$profileUserIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "프로필 친구 정보 조회 성공";

           echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfileImg":
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

            $profileUserIdx = $vars["idx"];
            $profileUserIdx = isset($profileUserIdx)?intval($profileUserIdx):null;

            if(is_null($profileUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isValidUserIdx($profileUserIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($profileUserIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserBlocked($userIdx,$profileUserIdx) == 1){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getProfileImg($userIdx,$profileUserIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "프로필 사진 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getProfileBackgroundImg":
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

            $profileUserIdx = $vars["idx"];
            $profileUserIdx = isset($profileUserIdx)?intval($profileUserIdx):null;

            if(is_null($profileUserIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "idx is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isValidUserIdx($profileUserIdx) == 0){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 타겟 idx입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($profileUserIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "idx 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserBlocked($userIdx,$profileUserIdx) == 1){
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "접근 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getProfileBackgroundImg($profileUserIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "프로필 사진 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
