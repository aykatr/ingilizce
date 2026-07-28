<?php

use App\Core\Migration;
use App\Helpers\Str;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE licenses
                ADD COLUMN code VARCHAR(20) NULL AFTER token,
                ADD COLUMN expires_at DATETIME NULL AFTER is_active,
                ADD COLUMN first_activated_at DATETIME NULL AFTER expires_at,
                ADD COLUMN last_used_at DATETIME NULL AFTER first_activated_at,
                ADD COLUMN last_device VARCHAR(255) NULL AFTER last_used_at,
                ADD COLUMN last_ip VARCHAR(45) NULL AFTER last_device'
        );

        $ids = $pdo->query('SELECT id FROM licenses')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($ids as $id) {
            $stmt = $pdo->prepare('UPDATE licenses SET code = :code WHERE id = :id');
            $stmt->execute(['code' => Str::code(), 'id' => $id]);
        }

        $pdo->exec('ALTER TABLE licenses MODIFY code VARCHAR(20) NOT NULL, ADD UNIQUE KEY licenses_code_unique (code)');
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec(
            'ALTER TABLE licenses
                DROP INDEX licenses_code_unique,
                DROP COLUMN code,
                DROP COLUMN expires_at,
                DROP COLUMN first_activated_at,
                DROP COLUMN last_used_at,
                DROP COLUMN last_device,
                DROP COLUMN last_ip'
        );
    }
};
