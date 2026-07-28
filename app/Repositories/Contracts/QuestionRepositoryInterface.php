<?php

namespace App\Repositories\Contracts;

interface QuestionRepositoryInterface
{
    public function allWithCategory(): array;

    public function activeOrdered(): array;

    public function find(int|string $id): ?array;

    public function create(array $data): string;

    public function update(int|string $id, array $data): bool;

    public function delete(int|string $id): bool;
}
