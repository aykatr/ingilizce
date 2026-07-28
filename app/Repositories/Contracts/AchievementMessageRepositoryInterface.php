<?php

namespace App\Repositories\Contracts;

interface AchievementMessageRepositoryInterface
{
    public function all(): array;

    public function find(int|string $id): ?array;

    public function activeByType(string $type): array;

    public function create(array $data): string;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
