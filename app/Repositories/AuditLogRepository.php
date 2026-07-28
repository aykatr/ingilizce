<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\AuditLog;
use App\Repositories\Contracts\AuditLogRepositoryInterface;

class AuditLogRepository implements AuditLogRepositoryInterface
{
    public function create(array $data): string
    {
        return AuditLog::create($data);
    }

    public function paginate(array $filters, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildWhere($filters);
        $offset = ($page - 1) * $perPage;

        $stmt = Database::connection()->prepare(
            "SELECT * FROM audit_logs {$where} ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset"
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function count(array $filters): int
    {
        [$where, $params] = $this->buildWhere($filters);

        $stmt = Database::connection()->prepare("SELECT COUNT(*) FROM audit_logs {$where}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    public function distinctActions(): array
    {
        $stmt = Database::connection()->query('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC');

        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        if (!empty($filters['action'])) {
            $conditions[] = 'action = :action';
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['q'])) {
            $conditions[] = '(description LIKE :q OR admin_username LIKE :q)';
            $params[':q'] = '%' . $filters['q'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = 'created_at >= :date_from';
            $params[':date_from'] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = 'created_at <= :date_to';
            $params[':date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        return [$where, $params];
    }
}
