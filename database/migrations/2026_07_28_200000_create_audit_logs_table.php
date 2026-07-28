<?php

use App\Core\Migration;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE audit_logs (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                admin_id INT UNSIGNED NULL,
                admin_username VARCHAR(100) NULL,
                action VARCHAR(100) NOT NULL,
                description VARCHAR(500) NOT NULL,
                ip_address VARCHAR(45) NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_audit_logs_action (action),
                KEY idx_audit_logs_admin_id (admin_id),
                KEY idx_audit_logs_created_at (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS audit_logs');
    }
};
