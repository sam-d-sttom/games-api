<?php

declare(strict_types=1);

//autoloading all files with a class based on class name
spl_autoload_register(fn($class) => require __DIR__."/src/${class}.php");

//setting error handler and exception handler with the ErrorHandler class.
set_error_handler("ErrorHandler::handleError");
set_exception_handler("ErrorHandler::handleException");


//setting initial end point.
$uri = explode('/', $_SERVER['REQUEST_URI']);

// var_dump($uri);

if($uri[2] != 'games'){
    http_response_code(404);
    exit;
}

header("Content-type: application/json; charset=UTF-8");

$id = $uri[3] ?? null;

$database = new Database('localhost', 'sttom', 'qwerty12345', 'games_db');

$gameGateway = new GameGateway($database);

$gameController = new GameController($gameGateway);
$gameController->processRequest($_SERVER["REQUEST_METHOD"], $id);