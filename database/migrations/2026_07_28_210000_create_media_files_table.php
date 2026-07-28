<?php

use App\Core\Migration;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE media_files (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                path VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NULL,
                type ENUM("image", "audio") NOT NULL,
                mime_type VARCHAR(100) NULL,
                extension VARCHAR(10) NOT NULL,
                size_bytes INT UNSIGNED NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_media_files_path (path),
                KEY idx_media_files_type (type)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS media_files');
    }
};
