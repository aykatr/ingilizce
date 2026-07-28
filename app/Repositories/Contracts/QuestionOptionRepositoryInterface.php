<?php

namespace App\Repositories\Contracts;

interface QuestionOptionRepositoryInterface
{
    public function findByQuestion(int|string $questionId): array;

    public function create(array $data): string;

    public function update(int|string $id, array $data): bool;
}
