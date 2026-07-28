<?php

namespace App\Repositories\Contracts;

interface AdminRepositoryInterface
{
    public function findById(int|string $id): ?array;

    public function findByUsername(string $username): ?array;

    public function updatePassword(int|string $id, string $hashedPassword): bool;
}
