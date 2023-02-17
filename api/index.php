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
$actorId = isset($_GET["actorId"]) ? htmlspecialchars($_GET["actorId"]) : null;
if (!$isActorEndpoint) {
    $moviesController = new MoviesController($conn, $requestMethod, $movieId, $page, $size);
    $moviesController->processRequest();
}
else {
    $actorsController = new ActorsController($conn, $requestMethod, $actorId, $page, $size);
    $actorsController->processRequest();
}

$conn->close();