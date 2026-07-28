<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\SettingRepository;
use App\Services\MediaUploadService;
use App\Services\StartScreenService;

class StartScreenController extends AdminBaseController
{
    private StartScreenService $startScreenService;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->startScreenService = new StartScreenService(new SettingRepository(), $media);
    }

    public function edit(): void
    {
        $this->view('admin.settings.start_screen', [
            'title' => 'Başlangıç Ekranı Ayarları',
            'startScreen' => $this->startScreenService->get(),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function update(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/settings/start-screen'));
        }

        $this->startScreenService->update($request->all(), $_FILES);

        Session::flash('success', 'Başlangıç ekranı ayarları güncellendi.');
        $this->redirect(base_url('admin/settings/start-screen'));
    }
}
