<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\TransitionMessage;
use App\Repositories\Contracts\TransitionMessageRepositoryInterface;

class TransitionMessageRepository implements TransitionMessageRepositoryInterface
{
    public function all(): array
    {
        return TransitionMessage::all();
    }

    public function find(int|string $id): ?array
    {
        return TransitionMessage::find($id);
    }

    public function activeMessages(): array
    {
        $stmt = Database::connection()->query('SELECT * FROM transition_messages WHERE is_active = 1');

        return $stmt->fetchAll();
    }

    public function create(array $data): string
    {
        return TransitionMessage::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return TransitionMessage::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return TransitionMessage::delete($id);
    }
}
