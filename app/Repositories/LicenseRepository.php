<?php

namespace App\Repositories;

use App\Models\License;
use App\Repositories\Contracts\LicenseRepositoryInterface;

class LicenseRepository implements LicenseRepositoryInterface
{
    public function all(): array
    {
        return License::all();
    }

    public function find(int|string $id): ?array
    {
        return License::find($id);
    }

    public function findByToken(string $token): ?array
    {
        return License::findByToken($token);
    }

    public function findByCode(string $code): ?array
    {
        return License::findByCode($code);
    }

    public function create(array $data): string
    {
        return License::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return License::update($id, $data);
    }

    public function recordUsage(int|string $id, array $data): bool
    {
        return License::update($id, $data);
    }
}
