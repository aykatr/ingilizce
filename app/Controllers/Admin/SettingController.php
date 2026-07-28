<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\SettingRepository;
use App\Services\Exceptions\ValidationException;
use App\Services\SettingService;

class SettingController extends AdminBaseController
{
    private SettingService $settingService;

    public function __construct()
    {
        parent::__construct();
        $this->settingService = new SettingService(new SettingRepository());
    }

    public function edit(): void
    {
        $this->view('admin.settings.edit', [
            'title' => 'Site Ayarları',
            'siteUrl' => $this->settingService->getSiteUrl(),
            'error' => Session::getFlash('error'),
            'success' => Session::getFlash('success'),
        ], 'admin');
    }

    public function update(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/settings'));
        }

        try {
            $this->settingService->updateSiteUrl((string) $request->input('site_url'));
            Session::flash('success', 'Site URL güncellendi.');
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect(base_url('admin/settings'));
    }
}
