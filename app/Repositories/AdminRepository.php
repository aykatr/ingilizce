<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Repositories\Contracts\AdminRepositoryInterface;

class AdminRepository implements AdminRepositoryInterface
{
    public function findById(int|string $id): ?array
    {
        return Admin::find($id);
    }

    public function findByUsername(string $username): ?array
    {
        return Admin::findByUsername($username);
    }

    public function updatePassword(int|string $id, string $hashedPassword): bool
    {
        return Admin::update($id, ['password' => $hashedPassword]);
    }
}
