<?php

namespace App\Repositories;

use App\Core\Database;
use App\Repositories\Contracts\SettingRepositoryInterface;

class SettingRepository implements SettingRepositoryInterface
{
    public function get(string $key): ?string
    {
        $stmt = Database::connection()->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);

        $value = $stmt->fetchColumn();

        return $value === false ? null : $value;
    }

    public function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (`key`, `value`) VALUES (:key, :value)
             ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)'
        );
        $stmt->execute(['key' => $key, 'value' => $value]);
    }

    public function delete(string $key): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM settings WHERE `key` = :key');
        $stmt->execute(['key' => $key]);
    }
}
