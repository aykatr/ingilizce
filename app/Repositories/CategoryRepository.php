<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function all(): array
    {
        return Category::all();
    }

    public function find(int|string $id): ?array
    {
        return Category::find($id);
    }

    public function create(array $data): string
    {
        return Category::create($data);
    }

    public function update(int|string $id, array $data): bool
    {
        return Category::update($id, $data);
    }

    public function delete(int|string $id): bool
    {
        return Category::delete($id);
    }

    public function hasQuestions(int|string $id): bool
    {
        $stmt = Database::connection()->prepare('SELECT COUNT(*) FROM questions WHERE category_id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }
}
