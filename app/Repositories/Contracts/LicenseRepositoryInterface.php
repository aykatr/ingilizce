<?php

namespace App\Repositories\Contracts;

interface LicenseRepositoryInterface
{
    public function all(): array;

    public function find(int|string $id): ?array;

    public function findByToken(string $token): ?array;

    public function findByCode(string $code): ?array;

    public function create(array $data): string;

    public function update(int|string $id, array $data): bool;

    public function recordUsage(int|string $id, array $data): bool;
}
