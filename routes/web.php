<?php

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LicenseController;
use App\Controllers\Admin\PasswordController;
use App\Controllers\HomeController;
use App\Core\Router;

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/health', [HomeController::class, 'health']);

$router->get('/admin/login', [AuthController::class, 'showLoginForm']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

$router->get('/admin/dashboard', [DashboardController::class, 'index']);

$router->get('/admin/password', [PasswordController::class, 'edit']);
$router->post('/admin/password', [PasswordController::class, 'update']);

$router->get('/admin/licenses', [LicenseController::class, 'index']);
$router->get('/admin/licenses/create', [LicenseController::class, 'create']);
$router->post('/admin/licenses', [LicenseController::class, 'store']);
$router->post('/admin/licenses/{id}/toggle', [LicenseController::class, 'toggle']);

return $router;
