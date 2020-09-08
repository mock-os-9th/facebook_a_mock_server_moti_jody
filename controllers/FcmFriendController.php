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

        case "setFcmTokenToUser":
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

            $token = $req->token;
            $token = isset($token)?$token:null;

            if(is_null($token)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "fcm token is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(gettype($token) != 'string'){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "fcm token 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            setFcmTokenToUser($token,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "토큰 입력 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
