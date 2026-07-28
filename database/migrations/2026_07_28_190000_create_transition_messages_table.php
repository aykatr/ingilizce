<?php

use App\Core\Migration;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE transition_messages (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(150) NOT NULL,
                audio VARCHAR(255) NULL,
                animation_type VARCHAR(50) NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS transition_messages');
    }
};
