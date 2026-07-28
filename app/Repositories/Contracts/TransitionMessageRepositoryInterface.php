<?php

namespace App\Repositories\Contracts;

interface TransitionMessageRepositoryInterface
{
    public function all(): array;

    public function find(int|string $id): ?array;

    public function activeMessages(): array;

    public function create(array $data): string;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
