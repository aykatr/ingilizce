<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Config;
use App\Core\Database;
use App\Core\Env;

Env::load(__DIR__ . '/../.env');
Config::load(__DIR__ . '/../config');

$pdo = Database::connection();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        batch INT UNSIGNED NOT NULL,
        run_at DATETIME NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
);

$applied = $pdo->query('SELECT migration FROM migrations')->fetchAll(PDO::FETCH_COLUMN);

$files = glob(__DIR__ . '/migrations/*.php');
sort($files);

$command = $argv[1] ?? 'migrate';

if ($command === 'migrate') {
    $batch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn() + 1;
    $ran = 0;

    foreach ($files as $file) {
        $name = basename($file, '.php');

        if (in_array($name, $applied, true)) {
            continue;
        }

        $migration = require $file;
        $migration->up($pdo);

        $stmt = $pdo->prepare('INSERT INTO migrations (migration, batch, run_at) VALUES (:migration, :batch, NOW())');
        $stmt->execute(['migration' => $name, 'batch' => $batch]);

        echo "Migrated: {$name}\n";
        $ran++;
    }

    echo $ran === 0 ? "Yeni migration yok.\n" : "{$ran} migration çalıştırıldı.\n";
} elseif ($command === 'rollback') {
    $lastBatch = (int) $pdo->query('SELECT COALESCE(MAX(batch), 0) FROM migrations')->fetchColumn();

    if ($lastBatch === 0) {
        echo "Geri alınacak migration yok.\n";
        exit;
    }

    $stmt = $pdo->prepare('SELECT migration FROM migrations WHERE batch = :batch ORDER BY id DESC');
    $stmt->execute(['batch' => $lastBatch]);

    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $name) {
        $file = __DIR__ . "/migrations/{$name}.php";

        if (!is_file($file)) {
            echo "Uyarı: {$name} dosyası bulunamadı, atlanıyor.\n";
            continue;
        }

        $migration = require $file;
        $migration->down($pdo);

        $del = $pdo->prepare('DELETE FROM migrations WHERE migration = :migration');
        $del->execute(['migration' => $name]);

        echo "Geri alındı: {$name}\n";
    }
} else {
    echo "Bilinmeyen komut: {$command}. Kullanım: php database/migrate.php [migrate|rollback]\n";
}
