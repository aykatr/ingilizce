<?php

namespace App\Services;

use App\Core\Database;
use PDO;

class BackupService
{
    public function generateSql(): string
    {
        $pdo = Database::connection();
        $output = "-- Yippee Learning Platform veritabanı yedeği\n";
        $output .= '-- Oluşturulma: ' . date('Y-m-d H:i:s') . "\n\n";
        $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createRow = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n{$createRow['Create Table']};\n\n";

            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $columns = '`' . implode('`, `', array_keys($row)) . '`';
                $values = implode(', ', array_map(
                    fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value),
                    array_values($row)
                ));

                $output .= "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});\n";
            }

            $output .= "\n";
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $output;
    }
}
