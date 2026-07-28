<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Badge;
use App\Repositories\Contracts\BadgeRepositoryInterface;

class BadgeRepository implements BadgeRepositoryInterface
{
    public function all(): array
    {
        return Badge::all();
    }

    public function find(int|string $id): ?array
    {
        return Badge::find($id);
    }

    public function findMany(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = Database::connection()->prepare("SELECT * FROM badges WHERE id IN ({$placeholders})");
        $stmt->execute(array_values($ids));

        return $stmt->fetchAll();
    }

    public function activeBadges(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM badges WHERE is_active = 1');

        return $stmt->fetchAll();
    }

    public function create(array $data): string
    {
        return Badge::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return Badge::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return Badge::delete($id);
    }
}
