<?php

use App\Controllers\Admin\AchievementMessageController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\BadgeController;
use App\Controllers\Admin\CategoryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LicenseController;
use App\Controllers\Admin\PasswordController;
use App\Controllers\Admin\QuestionController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\StartScreenController;
use App\Controllers\GameController;
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

$router->get('/admin/settings', [SettingController::class, 'edit']);
$router->post('/admin/settings', [SettingController::class, 'update']);
$router->get('/admin/settings/start-screen', [StartScreenController::class, 'edit']);
$router->post('/admin/settings/start-screen', [StartScreenController::class, 'update']);

$router->get('/admin/categories', [CategoryController::class, 'index']);
$router->get('/admin/categories/create', [CategoryController::class, 'create']);
$router->post('/admin/categories', [CategoryController::class, 'store']);
$router->get('/admin/categories/{id}/edit', [CategoryController::class, 'edit']);
$router->post('/admin/categories/{id}', [CategoryController::class, 'update']);
$router->post('/admin/categories/{id}/delete', [CategoryController::class, 'destroy']);

$router->get('/admin/questions', [QuestionController::class, 'index']);
$router->get('/admin/questions/create', [QuestionController::class, 'create']);
$router->post('/admin/questions', [QuestionController::class, 'store']);
$router->get('/admin/questions/{id}/edit', [QuestionController::class, 'edit']);
$router->post('/admin/questions/{id}', [QuestionController::class, 'update']);
$router->post('/admin/questions/{id}/delete', [QuestionController::class, 'destroy']);

$router->get('/admin/messages', [AchievementMessageController::class, 'index']);
$router->get('/admin/messages/create', [AchievementMessageController::class, 'create']);
$router->post('/admin/messages', [AchievementMessageController::class, 'store']);
$router->get('/admin/messages/{id}/edit', [AchievementMessageController::class, 'edit']);
$router->post('/admin/messages/{id}', [AchievementMessageController::class, 'update']);
$router->post('/admin/messages/{id}/delete', [AchievementMessageController::class, 'destroy']);

$router->get('/admin/badges', [BadgeController::class, 'index']);
$router->get('/admin/badges/create', [BadgeController::class, 'create']);
$router->post('/admin/badges', [BadgeController::class, 'store']);
$router->get('/admin/badges/{id}/edit', [BadgeController::class, 'edit']);
$router->post('/admin/badges/{id}', [BadgeController::class, 'update']);
$router->post('/admin/badges/{id}/delete', [BadgeController::class, 'destroy']);

$router->post('/play/api/start', [GameController::class, 'start']);
$router->post('/play/api/answer', [GameController::class, 'answer']);
$router->post('/play/api/timeout', [GameController::class, 'timeout']);

return $router;
