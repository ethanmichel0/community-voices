<?php

// Set-up
use CommunityVoices\App\Website\Bootstrap;

// Timezone
// @config
date_default_timezone_set('America/New_York');

// Error settings
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Composer autoloading
require __DIR__ . '/../../../../vendor/autoload.php';

// Injector
$injector = new Auryn\Injector;

// Routes
$routes = new Bootstrap\Routes;

// Logger
$logger = new Monolog\Logger('name');
$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/../../../../log/access.log'));

// Instantiate the front controller
$controller = new Bootstrap\FrontController(
    new Bootstrap\Router($routes->get()),
    new Bootstrap\Dispatcher($injector),
    $injector,
    $logger
);

// Start the request
$controller->doRequest(
    Symfony\Component\HttpFoundation\Request::createFromGlobals()
);
