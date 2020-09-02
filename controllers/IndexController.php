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

            if($phoneNum == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "phoneNum이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($pwd == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "pwd가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($secondName == null) {
                $res->isSuccess = FALSE;
                $res->code = 443;
                $res->message = "secondName이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($firstName == null) {
                $res->isSuccess = FALSE;
                $res->code = 444;
                $res->message = "firstName이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($bday == null) {
                $res->isSuccess = FALSE;
                $res->code = 445;
                $res->message = "bday이 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if($gender == null) {
                $res->isSuccess = FALSE;
                $res->code = 446;
                $res->message = "gender가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($email != null) {
                if(!is_string($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 411;
                    $res->message = "email은 String이여야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(!is_string($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "phoneNum은 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 413;
                $res->message = "pwd는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($secondName)) {
                $res->isSuccess = FALSE;
                $res->code = 414;
                $res->message = "secondName은 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($firstName)) {
                $res->isSuccess = FALSE;
                $res->code = 415;
                $res->message = "firstName는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 416;
                $res->message = "bday는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!is_string($gender)) {
                $res->isSuccess = FALSE;
                $res->code = 417;
                $res->message = "gender는 String이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($email != null) {
                if (!isValidEmail($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 431;
                    $res->message = "email @와 .을 포함해야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(!isValidPhoneNum($phoneNum)) {
                $res->isSuccess = FALSE;
                $res->code = 421;
                $res->message = "phoneNum은 + 또는 0~9 형태이며 길이는 11~14 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidPwd($pwd)) {
                $res->isSuccess = FALSE;
                $res->code = 433;
                $res->message = "pwd는 최소 6자 이상의 문자, 숫자 및 기호(예: !, %%)가 포함되어야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            if(!isValidDate($bday)) {
                $res->isSuccess = FALSE;
                $res->code = 434;
                $res->message = "bday는 YYYY-MM-DD 형식이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            if($email != null) {
                if (isEmailDuplicated($email)) {
                    $res->isSuccess = FALSE;
                    $res->code = 461;
                    $res->message = "이미 사용중인 email 입니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    break;
                }
            }
            if(isPhoneNumDuplicate($phoneNum)) {
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
        /*
         * API No. 5
         * API Name : 프로필 정보 가져오기 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUserInfo":
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

            $res->result = getUserInfo($idx);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
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

            if($targetIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($bound == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "bound가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(is_integer($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "idx는 int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!is_string($bound)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "bound는 String 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($targetIdx == 0) {
                $targetIdx = $idx;
            }
            else if(!isValidUserIdx($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "존재하지 않는 타겟 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }


            if(!isCareerIdxExists($targetIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "경력이 존재 하지 않습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(strlen($bound) != 1) {
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "bound의 길이는 1 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($targetIdx != $idx) {
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


            $userIdx = getUserIdxFromJwt($jwt,JWT_SECRET_KEY);


            if($userIdx == 0){
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

            if(gettype($commentIdx) != 'integer'){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(gettype($isLike) != 'string'){
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(gettype($likeIdx) != 'integer'){
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "댓글 인덱스 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(strlen($isLike) != 1){
                $res->isSuccess = FALSE;
                $res->code = 420;
                $res->message = "좋아요 여부 길이 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if($isLike != 'N' && $isLike != 'Y'){
                $res->isSuccess = FALSE;
                $res->code = 430;
                $res->message = "좋아요 여부 타입 오류";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($commentIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 인덱스가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($isLike)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "좋아요 여부가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(is_null($likeIdx)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "좋아요 인덱스가 null입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isValidCommentIdx($commentIdx)){
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserLikedComment($userIdx,$commentIdx)){
                modifyCommentLike($commentIdx,$userIdx,$likeIdx,$isLike);
            }else{
                makeCommentLike($commentIdx,$userIdx,$likeIdx);
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


            $userIdx = getUserIdxFromJwt($jwt,JWT_SECRET_KEY);

            if($userIdx == 0){
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(is_null($page)){
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(is_null($limit)){
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit is null";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(gettype($page) != 'integer'){
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(gettype($limit) != 'integer'){
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit의 타입이 잘못되었습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            $res->page = $page;
            $res->limit = $limit;
            $res->result = getMainFeed($page,$limit,$userIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "메인 피드 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
