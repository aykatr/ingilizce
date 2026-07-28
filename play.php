<?php

require __DIR__ . '/vendor/autoload.php';

use App\Core\Config;
use App\Core\Env;
use App\Core\Session;
use App\Core\View;
use App\Repositories\LicenseRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SettingRepository;
use App\Services\Exceptions\ValidationException;
use App\Services\LicenseService;
use App\Services\MediaUploadService;
use App\Services\StartScreenService;

Env::load(__DIR__ . '/.env');
Config::load(__DIR__ . '/config');

date_default_timezone_set(config('app.timezone', 'Europe/Istanbul'));

Session::start();

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

Session::put('play_authorized', true);

$media = new MediaUploadService(config('app.uploads_path'));
$startScreenService = new StartScreenService(new SettingRepository(), $media);

$questionRepository = new QuestionRepository();
$totalQuestions = count(array_filter(
    $questionRepository->allWithCategory(),
    fn (array $q) => (int) $q['is_active'] === 1
));

echo View::render('play.index', [
    'license' => $license,
    'startScreen' => $startScreenService->get(),
    'csrfToken' => Session::csrfToken(),
    'totalQuestions' => $totalQuestions,
], null);
