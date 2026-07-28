<?php

namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel
{
    protected static string $table;
    protected static string $primaryKey = 'id';

    protected static function db(): PDO
    {
        return Database::connection();
    }

    public static function all(): array
    {
        $stmt = static::db()->query('SELECT * FROM ' . static::$table);

        return $stmt->fetchAll();
    }

    public static function find(int|string $id): ?array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public static function where(string $column, mixed $value): array
    {
        $stmt = static::db()->prepare('SELECT * FROM ' . static::$table . ' WHERE ' . $column . ' = :value');
        $stmt->execute(['value' => $value]);

        return $stmt->fetchAll();
    }

    public static function create(array $data): string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn ($column) => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::$table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::db()->prepare($sql);
        $stmt->execute($data);

        return static::db()->lastInsertId();
    }

    public static function update(int|string $id, array $data): bool
    {
        $assignments = implode(', ', array_map(fn ($column) => "{$column} = :{$column}", array_keys($data)));

        $sql = sprintf('UPDATE %s SET %s WHERE %s = :id', static::$table, $assignments, static::$primaryKey);

        $stmt = static::db()->prepare($sql);

        return $stmt->execute([...$data, 'id' => $id]);
    }

    public static function delete(int|string $id): bool
    {
        $stmt = static::db()->prepare('DELETE FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = :id');

        return $stmt->execute(['id' => $id]);
    }
}
