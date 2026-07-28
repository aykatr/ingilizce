<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\View;
use App\Repositories\LicenseRepository;
use App\Services\Exceptions\ValidationException;
use App\Services\LicenseService;

Env::load(__DIR__ . '/.env');
Config::load(__DIR__ . '/config');

date_default_timezone_set(config('app.timezone', 'Europe/Istanbul'));

$token = trim((string) ($_GET['t'] ?? ''));
$licenseService = new LicenseService(new LicenseRepository());

try {
    if ($token === '') {
        throw new ValidationException('Lisans bulunamadı.');
    }

    $license = $licenseService->validateAndTrack(
        $token,
        $_SERVER['REMOTE_ADDR'] ?? '',
        $_SERVER['HTTP_USER_AGENT'] ?? null
    );
} catch (ValidationException $e) {
    http_response_code(403);
    echo View::render('play.invalid', ['message' => $e->getMessage()], null);
    exit;
}

echo View::render('play.index', ['license' => $license], null);
