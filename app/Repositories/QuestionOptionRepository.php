<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\QuestionOption;
use App\Repositories\Contracts\QuestionOptionRepositoryInterface;

class QuestionOptionRepository implements QuestionOptionRepositoryInterface
{
    public function findByQuestion(int|string $questionId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM question_options WHERE question_id = :qid ORDER BY position ASC'
        );
        $stmt->execute(['qid' => $questionId]);

        return $stmt->fetchAll();
    }

    public function create(array $data): string
    {
        return QuestionOption::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return QuestionOption::update($id, $data);
    }
}
