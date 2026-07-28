<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Exceptions\ValidationException;

class CategoryService
{
    public function __construct(private CategoryRepositoryInterface $categories)
    {
    }

    public function list(): array
    {
        return $this->categories->all();
    }

    public function find(int|string $id): ?array
    {
        return $this->categories->find($id);
    }

    public function create(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            throw new ValidationException('Kategori adı gerekli.');
        }

        $id = $this->categories->create(['name' => $name]);

        return $this->categories->find($id);
    }

    public function update(int|string $id, string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new ValidationException('Kategori adı gerekli.');
        }

        $this->categories->update($id, ['name' => $name]);
    }

    public function delete(int|string $id): void
    {
        if ($this->categories->hasQuestions($id)) {
            throw new ValidationException('Bu kategoriye bağlı sorular olduğu için silinemez.');
        }

        $this->categories->delete($id);
    }
}
