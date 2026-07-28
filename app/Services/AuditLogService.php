<?php

namespace App\Services;

use App\Core\Auth;
use App\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogService
{
    public function __construct(private AuditLogRepositoryInterface $logs)
    {
    }

    public function record(string $action, string $description): void
    {
        $admin = Auth::user();

        $this->logs->create([
            'admin_id' => $admin['id'] ?? null,
            'admin_username' => $admin['username'] ?? null,
            'action' => $action,
            'description' => $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function paginate(array $filters, int $page, int $perPage = 30): array
    {
        $page = max(1, $page);
        $total = $this->logs->count($filters);

        return [
            'items' => $this->logs->paginate($filters, $page, $perPage),
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function actions(): array
    {
        return $this->logs->distinctActions();
    }
}
