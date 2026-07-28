<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\LicenseRepository;
use App\Repositories\SettingRepository;
use App\Services\AuditLogService;
use App\Services\Exceptions\ValidationException;
use App\Services\LicenseService;
use App\Services\SettingService;

class LicenseController extends AdminBaseController
{
    private LicenseService $licenseService;
    private SettingService $settingService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();
        $this->licenseService = new LicenseService(new LicenseRepository());
        $this->settingService = new SettingService(new SettingRepository());
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function index(): void
    {
        $siteUrl = $this->settingService->getSiteUrl();

        $licenses = array_map(function (array $license) use ($siteUrl) {
            $license['status_label'] = $this->licenseService->statusLabel($license);
            $license['play_url'] = $siteUrl . '/play.php?t=' . $license['token'];

            return $license;
        }, $this->licenseService->list());

        $this->view('admin.licenses.index', [
            'title' => 'Lisanslar',
            'licenses' => $licenses,
            'success' => Session::getFlash('success'),
            'newLink' => Session::getFlash('license_link'),
        ], 'admin');
    }

    public function create(): void
    {
        $this->view('admin.licenses.create', [
            'title' => 'Yeni Lisans',
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function store(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/licenses/create'));
        }

        $expiresAtInput = trim((string) $request->input('expires_at'));

        try {
            $license = $this->licenseService->create(
                (string) $request->input('name'),
                $expiresAtInput !== '' ? $expiresAtInput . ' 23:59:59' : null
            );
        } catch (ValidationException $e) {
            Session::flash('error', $e->getMessage());
            $this->redirect(base_url('admin/licenses/create'));
        }

        $siteUrl = $this->settingService->getSiteUrl();
        $this->auditLog->record('license.create', "'{$license['name']}' lisansı oluşturuldu.");
        Session::flash('success', 'Lisans oluşturuldu.');
        Session::flash('license_link', $siteUrl . '/play.php?t=' . $license['token']);
        $this->redirect(base_url('admin/licenses'));
    }

    public function toggle(int|string $id): void
    {
        $request = new Request();

        if (Session::verifyCsrf($request->input('_csrf'))) {
            $this->licenseService->toggleStatus($id);
            $this->auditLog->record('license.toggle', "Lisans durumu değiştirildi (#{$id}).");
        }

        $this->redirect(base_url('admin/licenses'));
    }
}
