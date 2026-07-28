<?php

use App\Core\Migration;

return new class extends Migration {
    public function up(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE questions (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                category_id INT UNSIGNED NOT NULL,
                title VARCHAR(150) NOT NULL,
                card_image VARCHAR(255) NULL,
                card_audio VARCHAR(255) NULL,
                question_text VARCHAR(255) NOT NULL,
                question_audio VARCHAR(255) NULL,
                duration_seconds SMALLINT UNSIGNED NULL,
                points SMALLINT UNSIGNED NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                sort_order INT UNSIGNED NOT NULL DEFAULT 0,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_questions_category FOREIGN KEY (category_id) REFERENCES categories(id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
    }

    public function down(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS questions');
    }
};
