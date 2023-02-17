<?php

include_once(__DIR__ . '/configs/database_config.php');
include_once(__DIR__ . '/controllers/movies_controller.php');
include_once(__DIR__ . '/controllers/actors_controller.php');

use api\configs\DatabaseConfig;
use api\controllers\ActorsController;
use api\controllers\MoviesController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri));

// all of our endpoints start with /person
// everything else results in a 404 Not Found
if ($uri[2] !== 'actors') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

// the user id is, of course, optional and must be a number:
$movieId = null;
$isActorEndpoint = false;

if ( $uri[2] === 'movies') {
    $isActorEndpoint = false;
}else if($uri[2] === 'actors'){
    $isActorEndpoint = true;
}

$db = new DatabaseConfig();
$conn = $db->connect();
$requestMethod = $_SERVER["REQUEST_METHOD"];
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$size = isset($_GET['size']) ? intval($_GET['size']) : 5;
$movieId = isset($_GET["movieId"]) ? htmlspecialchars($_GET["movieId"]) : null;

if (!$isActorEndpoint) {
    $moviesController = new MoviesController($conn, $requestMethod, $movieId, $page, $size);
    $moviesController->processRequest();
}
else {
    var_dump("got here");
    $actorsController = new ActorsController($conn, $requestMethod, $movieId, $page, $size);
    $actorsController->processRequest();
}

//$requestMethod = $_SERVER["REQUEST_METHOD"];
//if ($isCommentEndpoint) {
//    // comment controller
//    // product_id, comment_id, method
//    error_log("In comment\n");
//
//} else {
//    // product controller
//    // product_id, comment_id, method
//    error_log("In product\n");
//
//    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
//    $size = isset($_GET['size']) ? intval($_GET['size']) : 5;
//
//    $controller = new MoviesController($conn, $requestMethod, $movieId, $page, $size);
//    $controller->processRequest();
//}

$conn->close();