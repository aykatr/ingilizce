<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\AchievementMessage;
use App\Repositories\Contracts\AchievementMessageRepositoryInterface;

class AchievementMessageRepository implements AchievementMessageRepositoryInterface
{
    public function all(): array
    {
        return AchievementMessage::all();
    }

    public function find(int|string $id): ?array
    {
        return AchievementMessage::find($id);
    }

    public function activeByType(string $type): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM achievement_messages WHERE type = :type AND is_active = 1'
        );
        $stmt->execute(['type' => $type]);

        return $stmt->fetchAll();
    }

    public function create(array $data): string
    {
        return AchievementMessage::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return AchievementMessage::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return AchievementMessage::delete($id);
    }
}
