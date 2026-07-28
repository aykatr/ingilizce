<?php

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Repositories\AuditLogRepository;
use App\Services\AuditLogService;

class AuditLogController extends AdminBaseController
{
    private AuditLogService $auditLogService;

    public function __construct()
    {
        parent::__construct();

        $this->auditLogService = new AuditLogService(new AuditLogRepository());
    }

    public function index(): void
    {
        $request = new Request();

        $filters = [
            'action' => trim((string) $request->input('action', '')),
            'q' => trim((string) $request->input('q', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $page = max(1, (int) $request->input('page', 1));
        $result = $this->auditLogService->paginate($filters, $page);

        $this->view('admin.audit_log.index', [
            'title' => 'Denetim Kaydı',
            'logs' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'lastPage' => $result['lastPage'],
            'actions' => $this->auditLogService->actions(),
            'filters' => $filters,
        ], 'admin');
    }
}
