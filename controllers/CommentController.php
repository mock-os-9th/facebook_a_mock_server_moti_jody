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

        case "getLikeComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            $page = $_GET["page"];
            $page = isset($page) ? intval($page) : null;
            $limit = $_GET["limit"];
            $limit = isset($limit) ? intval($limit) : null;

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($commentIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($page) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($limit) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "댓글 idx는 Int 이여야 합니다";
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
            if(!isCommentOrReplyExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다	";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isCommentLikeExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "조회 할 좋아요가 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getCommentLike($userIdx, $commentIdx, $page, $limit);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 좋아요 리스트 조회 성공";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "likeComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isCommentOrReplyExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserLikedComment($userIdx, $commentIdx)){ //좋아요 되어 있을 때
                likeComment($userIdx, $commentIdx);
                $res->isLiked = 'N';
            }else{ //좋아요 안되어 있을 때
                if(isCommentLikeExistOnUser($userIdx, $commentIdx)) {
                    modifyCommentLike($userIdx, $commentIdx);
                }
                else {
                    makeCommentLike($userIdx, $commentIdx);
                }
                $res->isLiked = 'Y';
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 좋아요/좋아요 취소 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $postIdx = $vars["idx"];
            $postIdx = isset($postIdx) ? intval($postIdx) : null;

            $page = $_GET["page"];
            $page = isset($page) ? intval($page) : null;
            $limit = $_GET["limit"];
            $limit = isset($limit) ? intval($limit) : null;

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($postIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "게시물 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($page) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($limit) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_integer($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "게시물 idx는 Int 이여야 합니다";
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
            if(!isPostExist($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시물 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isPostCommentExist($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "조회 할 댓글이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getComment($userIdx, $postIdx, $page, $limit);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 조회 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getCommentReply":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            $page = $_GET["page"];
            $page = isset($page) ? intval($page) : null;
            $limit = $_GET["limit"];
            $limit = isset($limit) ? intval($limit) : null;

            if (is_null($page)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "page가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($limit)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "limit가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if ($commentIdx == null) {
                $res->isSuccess = FALSE;
                $res->code = 442;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($page) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "page는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (gettype($limit) != 'integer') {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "limit는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 412;
                $res->message = "댓글 idx는 Int 이여야 합니다";
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
            if(!isCommentExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!isCommentReplyExistOnComment($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 452;
                $res->message = "조회 할 답글이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $res->result = getCommentReply($userIdx, $commentIdx, $page, $limit);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "답글 조회 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $postIdx = $vars["postIdx"];
            $postIdx = isset($postIdx) ? intval($postIdx) : null;

            $commentContent = isset($req->commentContent) ? $req->commentContent : null;
            $commentImgUrl = isset($req->commentImgUrl) ? $req->commentImgUrl : null;

            if (is_null($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "postIdx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "commentContent가 null 입니다가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "postIdx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_string($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "commentContent는 String 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!is_null($commentImgUrl)) {
                if (!is_string($commentImgUrl)) {
                    $res->isSuccess = FALSE;
                    $res->code = 412;
                    $res->message = "commentImgUrl은 String 이여야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if(!isPostExist($postIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 게시물 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            //$res->commentIdx = createComment($userIdx, $postIdx, $commentContent, $commentImgUrl);
            echo getNameFromIdx($userIdx);
            send_comment_noti($userIdx, $postIdx, $commentContent);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 등록 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createCommentReply":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["commentIdx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            $commentContent = isset($req->commentContent) ? $req->commentContent : null;
            $commentImgUrl = isset($req->commentImgUrl) ? $req->commentImgUrl : null;

            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "commentIdx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "commentContent가 null 입니다가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "commentIdx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_string($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "commentContent는 String 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if(!is_null($commentImgUrl)) {
                if (!is_string($commentImgUrl)) {
                    $res->isSuccess = FALSE;
                    $res->code = 412;
                    $res->message = "commentImgUrl은 String 이여야 합니다";
                    echo json_encode($res, JSON_NUMERIC_CHECK);
                    addErrorLogs($errorLogs, $res, $req);
                    return;
                }
            }

            if(!isCommentExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $postIdx = getPostIdxByCommentIdx($commentIdx);

            $res->commentIdx = createCommentReply($userIdx, $postIdx, $commentIdx, $commentContent, $commentImgUrl);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "답글 등록 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "editComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            $commentContent = isset($req->commentContent) ? $req->commentContent : null;

            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (is_null($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 441;
                $res->message = "commentContent가 null 입니다가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }
            if (!is_string($commentContent)) {
                $res->isSuccess = FALSE;
                $res->code = 411;
                $res->message = "commentContent는 String 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isCommentOrReplyExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($userIdx != getCommentUserIdx($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "수정할 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            editComment($commentContent, $commentIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 수정 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isCommentOrReplyExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if($userIdx != getCommentUserIdx($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 470;
                $res->message = "삭제할 권한이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            deleteComment($commentIdx);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 삭제 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "hideComment":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                $res->isSuccess = FALSE;
                $res->code = 450;
                $res->message = "존재하지 않는 유저입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            $data = getDataByJWToken($jwt, JWT_SECRET_KEY);
            $userIdx = getUserIdxFromId($data->id);

            $commentIdx = $vars["idx"];
            $commentIdx = isset($commentIdx) ? intval($commentIdx) : null;

            if (is_null($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 440;
                $res->message = "댓글 idx가 null 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if (!is_integer($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 410;
                $res->message = "댓글 idx는 Int 이여야 합니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(!isCommentOrReplyExist($commentIdx)) {
                $res->isSuccess = FALSE;
                $res->code = 451;
                $res->message = "존재하지 않는 댓글 idx 입니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            if(isUserHidedComment($userIdx, $commentIdx)){ //숨김 되어 있을 떄
                showComment($userIdx, $commentIdx);
                $res->isHided = 'N';
            }
            else{ //볼 수 있게 되어 있을 때
                if(isCommentHideExistOnUser($userIdx, $commentIdx)) {
                    modifyCommentHide($userIdx, $commentIdx);
                }
                else {
                    makeCommentHide($userIdx, $commentIdx);
                }
                $res->isHided = 'Y';
            }

            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "댓글 숨김/숨김 해제 완료";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
