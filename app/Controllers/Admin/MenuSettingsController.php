<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\QuestionOptionRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\SettingRepository;
use App\Services\AuditLogService;
use App\Services\MediaUploadService;
use App\Services\MenuSettingsService;
use App\Services\QuestionService;

class MenuSettingsController extends AdminBaseController
{
    private MenuSettingsService $menuSettingsService;
    private QuestionService $questionService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();

        $media = new MediaUploadService(config('app.uploads_path'));
        $this->menuSettingsService = new MenuSettingsService(new SettingRepository(), $media);
        $this->questionService = new QuestionService(new QuestionRepository(), new QuestionOptionRepository(), $media);
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function edit(): void
    {
        $this->view('admin.settings.menu', [
            'title' => 'Menü Yönetimi',
            'menuSettings' => $this->menuSettingsService->get(),
            'questions' => $this->questionService->list(),
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error'),
        ], 'admin');
    }

    public function update(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/settings/menu'));
        }

        $this->menuSettingsService->update($request->all(), $_FILES);
        $this->auditLog->record('setting.menu_update', 'Kart Seçim Menüsü ayarları güncellendi.');

        Session::flash('success', 'Menü ayarları güncellendi.');
        $this->redirect(base_url('admin/settings/menu'));
    }

    public function updateCards(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/settings/menu'));
        }

        $rows = $request->input('cards', []);
        $this->questionService->updateOrder(is_array($rows) ? $rows : []);
        $this->auditLog->record('setting.menu_cards_update', 'Kart sırası/görünürlüğü güncellendi.');

        Session::flash('success', 'Kartlar güncellendi.');
        $this->redirect(base_url('admin/settings/menu'));
    }
}
