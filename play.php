<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\View;
use App\Models\License;

Env::load(__DIR__ . '/.env');
Config::load(__DIR__ . '/config');

date_default_timezone_set(config('app.timezone', 'Europe/Istanbul'));

$token = trim((string) ($_GET['token'] ?? ''));
$license = $token !== '' ? License::findByToken($token) : null;

if (!$license || !$license['is_active']) {
    http_response_code(403);
    echo View::render('play.invalid', [], null);
    exit;
}

echo View::render('play.index', ['license' => $license], null);
