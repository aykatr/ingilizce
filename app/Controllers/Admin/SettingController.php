<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\SettingRepository;
use App\Services\AuditLogService;
use App\Services\Exceptions\ValidationException;
use App\Services\SettingService;

class SettingController extends AdminBaseController
{
    private SettingService $settingService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();
        $this->settingService = new SettingService(new SettingRepository());
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function edit(): void
    {
        $this->view('admin.settings.edit', [
            'title' => 'Site Ayarları',
            'siteUrl' => $this->settingService->getSiteUrl(),
            'defaultDuration' => $this->settingService->getDefaultDuration(),
            'defaultPoints' => $this->settingService->getDefaultPoints(),
            'defaultLives' => $this->settingService->getDefaultLives(),
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
            $this->settingService->updateDefaults(
                (int) $request->input('default_duration_seconds', 30),
                (int) $request->input('default_points', 10),
                (int) $request->input('default_lives', 3)
            );
            $this->auditLog->record('setting.update', 'Site ayarları güncellendi.');
            Session::flash('success', 'Ayarlar güncellendi.');
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
        }

        $this->redirect(base_url('admin/settings'));
    }
}
