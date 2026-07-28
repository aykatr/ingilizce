<?php

namespace App\Repositories\Contracts;

interface MediaFileRepositoryInterface
{
    public function all(array $filters): array;

    public function allPaths(): array;

    public function find(int|string $id): ?array;

    public function findByPath(string $path): ?array;

    public function create(array $data): string;

    public function updateByPath(string $path, array $data): bool;

    public function delete(int|string $id): bool;

    public function deleteByPath(string $path): bool;

    public function usages(string $path): array;
}
