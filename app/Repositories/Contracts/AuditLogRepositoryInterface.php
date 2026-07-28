<?php

namespace App\Repositories\Contracts;

interface AuditLogRepositoryInterface
{
    public function create(array $data): string;

    public function paginate(array $filters, int $page, int $perPage): array;

    public function count(array $filters): int;

    public function distinctActions(): array;
}
