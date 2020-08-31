<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
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

            if(isNull($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "phoneNum이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isNull($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "pwd가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isNull($secondName)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "secondName이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isNull($firstName)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "firstName이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isNull($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "bday이 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(isNull($gender)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "gender가 null 입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isNull($email)) {
                if(!isString($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 410;
                    $res->message = "email은 String이여야 합니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(!isString($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "phoneNum은 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isString($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "pwd는 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isString($secondName)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "secondName은 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isString($firstName)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "firstName는 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isString($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "bday는 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isString($gender)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "gender는 String이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isNull($email)) {
                if (!isValidEmail($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 430;
                    $res->message = "email 형식이 올바르지 않습니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(!isValidPhoneNum($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "phoneNum은 11자 입력 되어야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPwd($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "비밀번호에는 최소 6자 이상의 문자, 숫자 및 기호(예: !, %%)가 포함되어야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidDate($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "bday는 YYYY-MM-DD 형식이여야 합니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if(!isNull($email)) {
                if (isEmailDuplicated($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 460;
                    $res->message = "이미 사용중인 email입니다.";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(isPhoneNumDuplicate($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 460;
                $res->message = "이미 사용중인 phoneNum입니다.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = createUser($email, $phoneNum, $pwd, $secondName, $firstName, $bday, $gender);

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "회원가입 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 2
         * API Name : 회원탈퇴 API
         * 마지막 수정 날짜 : 20.08.31
         */
        case "deleteUser":
            http_response_code(200);
            $res->result = deleteUser($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "회원탈퇴 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
