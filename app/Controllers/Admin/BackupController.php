<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Session;
use App\Repositories\AuditLogRepository;
use App\Services\AuditLogService;
use App\Services\BackupService;

class BackupController extends AdminBaseController
{
    private BackupService $backupService;
    private AuditLogService $auditLog;

    public function __construct()
    {
        parent::__construct();

        $this->backupService = new BackupService();
        $this->auditLog = new AuditLogService(new AuditLogRepository());
    }

    public function index(): void
    {
        $this->view('admin.backup.index', [
            'title' => 'Yedekleme',
        ], 'admin');
    }

    public function download(): void
    {
        $request = new Request();

        if (!Session::verifyCsrf($request->input('_csrf'))) {
            Session::flash('error', 'Oturum doğrulaması başarısız, tekrar deneyin.');
            $this->redirect(base_url('admin/backup'));
        }

        $sql = $this->backupService->generateSql();
        $this->auditLog->record('backup.create', 'Veritabanı yedeği indirildi.');

        $filename = 'ingilizce-backup-' . date('Y-m-d-His') . '.sql';

        header('Content-Type: application/sql; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        echo $sql;
        exit;
    }
}
