<?php

require_once 'core/Router.php';
require_once 'core/Database.php';
require_once 'config/constants.php';
require_once 'core/Middleware.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit();
}
;


$router = new Router();

$requestUrl = $_GET['url'] ?? '';


if (strpos($requestUrl, 'api/') === 0) {
    
    if (strpos($requestUrl, 'api/auth/') === 0) {
        // No middleware needed for login/register/logo
    }
    elseif (strpos($requestUrl, 'api/department/') === 0) {
        $router->addMiddleware([Middleware::class, 'authenticate']);
        $router->addMiddleware([Middleware::class, 'adminOnly']);
    }
    else {
        $router->addMiddleware([Middleware::class, 'authenticate']);
    }
}

$router->dispatch();