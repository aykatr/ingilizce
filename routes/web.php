<?php

use App\Controllers\Admin\AuthController;
use App\Controllers\HomeController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', [HomeController::class, 'health']);

$router->get('/admin/login', [AuthController::class, 'showLoginForm']);

return $router;
