<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Question;
use App\Repositories\Contracts\QuestionRepositoryInterface;

class QuestionRepository implements QuestionRepositoryInterface
{
    public function allWithCategory(): array
    {
        $sql = 'SELECT q.*, c.name AS category_name
                FROM questions q
                INNER JOIN categories c ON c.id = q.category_id
                ORDER BY q.sort_order ASC, q.id ASC';

        return Database::connection()->query($sql)->fetchAll();
    }

    public function activeOrdered(): array
    {
        $sql = 'SELECT q.*, c.name AS category_name
                FROM questions q
                INNER JOIN categories c ON c.id = q.category_id
                WHERE q.is_active = 1
                ORDER BY q.sort_order ASC, q.id ASC';

        return Database::connection()->query($sql)->fetchAll();
    }

    public function find(int|string $id): ?array
    {
        return Question::find($id);
    }

    public function create(array $data): string
    {
        return Question::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return Question::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return Question::delete($id);
    }
}
