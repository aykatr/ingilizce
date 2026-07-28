<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AchievementMessageRepository;
use App\Repositories\BadgeRepository;
use App\Repositories\QuestionOptionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SettingRepository;
use App\Services\AchievementMessageService;
use App\Services\BadgeService;
use App\Services\Exceptions\ValidationException;
use App\Services\GameSessionService;
use App\Services\MediaUploadService;
use App\Services\SettingService;

class GameController extends BaseController
{
    private GameSessionService $gameService;

    public function __construct()
    {
        $media = new MediaUploadService(config('app.uploads_path'));

        $this->gameService = new GameSessionService(
            new QuestionRepository(),
            new QuestionOptionRepository(),
            new SettingService(new SettingRepository()),
            new AchievementMessageService(new AchievementMessageRepository(), $media),
            new BadgeService(new BadgeRepository(), $media)
        );
    }

    public function start(): void
    {
        $this->guardAuthorized();
        $this->guardCsrf();

        $this->json($this->gameService->start());
    }

    public function answer(): void
    {
        $this->guardAuthorized();
        $this->guardCsrf();

        $request = new Request();
        $position = $request->input('position');

        try {
            $this->json($this->gameService->answer($position !== null ? (string) $position : null));
        } catch (ValidationException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    public function timeout(): void
    {
        $this->guardAuthorized();
        $this->guardCsrf();

        try {
            $this->json($this->gameService->answer(null, true));
        } catch (ValidationException $e) {
            $this->json(['error' => $e->getMessage()], 422);
        }
    }

    private function guardAuthorized(): void
    {
        if (!Session::get('play_authorized')) {
            $this->json(['error' => 'Yetkisiz erişim.'], 403);
        }
    }

    private function guardCsrf(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            $this->json(['error' => 'Oturum doğrulaması başarısız.'], 419);
        }
    }
}
