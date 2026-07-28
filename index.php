<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\Request;
use App\Core\Router;

Env::load(__DIR__ . '/.env');
Config::load(__DIR__ . '/config');

date_default_timezone_set(config('app.timezone', 'Europe/Istanbul'));

if (config('app.debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

session_start();

/** @var Router $router */
$router = require __DIR__ . '/routes/web.php';

$request = new Request();
$router->dispatch($request->method(), $request->uri());
