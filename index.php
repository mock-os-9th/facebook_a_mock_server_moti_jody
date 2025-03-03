<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/CommentPdo.php';
require './pdos/FriendPdo.php';
require './pdos/PostPdo.php';
require './pdos/UserPdo.php';
require './pdos/FcmPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);`

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('DELETE', '/user', ['IndexController', 'deleteUser']);

    $r->addRoute('GET', '/user/{idx}/profile/info', ['UserController', 'getProfileInfo']);
    $r->addRoute('GET', '/user/{idx}/profile/friend', ['UserController', 'getProfileFriend']);
    $r->addRoute('GET', '/user/{idx}/profile/img', ['UserController', 'getProfileImg']);
    $r->addRoute('GET', '/user/{idx}/profile/background', ['UserController', 'getProfileBackgroundImg']);


    $r->addRoute('POST', '/friend/{idx}/request', ['FriendController', 'requestFriend']);
    $r->addRoute('POST', '/friend/{idx}', ['FriendController', 'acceptFriendRequest']);
    $r->addRoute('DELETE', '/friend/{idx}/reject', ['FriendController', 'rejectFriendRequest']);
    $r->addRoute('GET', '/user/{idx}/friend', ['FriendController', 'getUserFriendList']);
    $r->addRoute('PATCH', '/friend/{idx}/block', ['FriendController', 'blockUser']);
    $r->addRoute('PATCH', '/friend/{idx}/follow', ['FriendController', 'followUser']);
    $r->addRoute('PATCH', '/friend/{idx}/unfollow', ['FriendController', 'unfollowUser']);
    $r->addRoute('DELETE', '/friend/{idx}', ['FriendController', 'deleteFriend']);
    $r->addRoute('GET', '/friend/{idx}/together', ['FriendController', 'getKnownFriendList']);
    $r->addRoute('GET', '/friend/request', ['FriendController', 'getRequestedFriendList']);
    $r->addRoute('GET', '/friend/{idx}', ['FriendController', 'searchFriend']);

    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
    $r->addRoute('POST', '/login', ['MainController', 'createJwt']);

    $r->addRoute('GET','/posts', ['PostController','getMainFeed']);
    $r->addRoute('POST','/posts',['PostController','createPost']);
    $r->addRoute('GET','/user/{idx}/posts',['PostController','getPersonalFeed']);
    $r->addRoute('GET','/posts/{idx}',['PostController','getOnePost']);
    $r->addRoute('PATCH','/posts/{idx}',['PostController','editPost']);
    $r->addRoute('DELETE','/posts/{idx}',['PostController','deletePost']);
    $r->addRoute('POST','/posts/{idx}/like',['PostController','postLikePush']);
    $r->addRoute('GET','/posts/{idx}/like',['PostController','getPostLikeList']);
    $r->addRoute('POST','/hide/posts/{idx}',['PostController','hidePost']);
    $r->addRoute('POST','/posts/{idx}/share',['PostController','sharePost']);
    $r->addRoute('POST','/posts/{idx}/notification',['PostController','postNotification']);

    $r->addRoute('GET','/comment/{idx}/like',['CommentController','getLikeComment']);
    $r->addRoute('POST','/comment/{idx}/like',['CommentController','likeComment']);
    $r->addRoute('GET','/post/{idx}/comment',['CommentController','getComment']);
    $r->addRoute('GET','/comment/{idx}',['CommentController','getCommentReply']);
    $r->addRoute('POST','/post/{postIdx}/comment',['CommentController','createComment']);
    $r->addRoute('POST','/comment/{commentIdx}/reply',['CommentController','createCommentReply']);
    $r->addRoute('PATCH','/comment/{idx}',['CommentController','editComment']);
    $r->addRoute('DELETE','/comment/{idx}',['CommentController','deleteComment']);
    $r->addRoute('PATCH','/comment/{idx}/hide',['CommentController','hideComment']);


    $r->addRoute('POST','/fcm',['FcmController','setFcmTokenToUser']);
    $r->addRoute('GET','/fcm/friends-recommend',['FcmController','getRecommendFriendFcm']);

    $r->addRoute('GET','/notification',['UserController','getUserNotification']);


//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'CommentController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/CommentController.php';
                break;
            case 'FriendController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/FriendController.php';
                break;
            case 'PostController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/PostController.php';
                break;
            case 'UserController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/UserController.php';
                break;
            case 'FcmController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/FcmController.php';
                break;
        }

        break;
}
