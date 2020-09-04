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
         * API No. 1
         * API Name : 회원가입 API
         * 마지막 수정 날짜 : 20.08.31
         */
        case "createUser":
            http_response_code(200);

            $email = isset($req->email) ? $req->email : null;
            $phoneNum = isset($req->phoneNum) ? $req->phoneNum : null;
            $pwd = isset($req->pwd) ? $req->pwd : null;
            $secondName = isset($req->secondName) ? $req->secondName : null;
            $firstName = isset($req->firstName) ? $req->firstName : null;
            $bday = isset($req->bday) ? $req->bday : null;
            $gender = isset($req->bday) ? $req->bday : null;

            if ($phoneNum == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "phoneNum이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if ($pwd == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "pwd가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if ($secondName == null) {
                $res->isSuccess = FALSE;
                $res->code = 443;
                $res->message = "secondName이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if ($firstName == null) {
                $res->isSuccess = FALSE;
                $res->code = 444;
                $res->message = "firstName이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if ($bday == null) {
                $res->isSuccess = FALSE;
                $res->code = 445;
                $res->message = "bday이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if ($gender == null) {
                $res->isSuccess = FALSE;
                $res->code = 446;
                $res->message = "gender가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($email != null) {
                if (!is_string($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 411;
                    $res->message = "email은 String이여야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if (!is_string($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "phoneNum은 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!is_string($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "pwd는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!is_string($secondName)) {
                $res->isSuccess = FALSE;
                $res->code = 414;
                $res->message = "secondName은 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!is_string($firstName)) {
                $res->isSuccess = FALSE;
                $res->code = 415;
                $res->message = "firstName는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!is_string($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 416;
                $res->message = "bday는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!is_string($gender)) {
                $res->isSuccess = FALSE;
                $res->code = 417;
                $res->message = "gender는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($email != null) {
                if (!isValidEmail($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 431;
                    $res->message = "email @와 .을 포함해야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if (!isValidPhoneNum($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 421;
                $res->message = "phoneNum은 + 또는 0~9 형태이며 길이는 11~14 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!isValidPwd($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 433;
                $res->message = "pwd는 최소 6자 이상의 문자, 숫자 및 기호(예: !, %%)가 포함되어야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if (!isValidDate($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 434;
                $res->message = "bday는 YYYY-MM-DD 형식이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if ($email != null) {
                if (isEmailDuplicated($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 461;
                    $res->message = "이미 사용중인 email 입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if (isPhoneNumDuplicate($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 462;
                $res->message = "이미 사용중인 phoneNum 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->userIdx = createUser($email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 4
         * API Name : 회원탈퇴 API
         * 마지막 수정 날짜 : 20.08.31
         */
        case "deleteUser":

            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 400;
                $res->message = "존재하지 않는 회원이거나 이미 탈퇴한 회원 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);

            $id = $data->id;
//            $pw = $data->pw;
//
//            if(!isValidUser($id, $pw)) {
//                $res->isSuccess = FALSE;
//                $res->code = 400;
//                $res->message = "존재하지 않는 회원이거나 이미 탈퇴한 회원입니다";
//                echo json_encode($res, JSON_NUMERIC_CHECK);
//                addErrorLogs($errorLogs, $res, $req);
//                return;
//            }

            $res->result = deleteUser($id);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "회원탈퇴 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
