<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $driver = Config::get('database.driver', 'mysql');
            $host = Config::get('database.host', '127.0.0.1');
            $port = Config::get('database.port', '3306');
            $database = Config::get('database.database');
            $charset = Config::get('database.charset', 'utf8mb4');

            $dsn = "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";

            try {
                self::$connection = new PDO(
                    $dsn,
                    Config::get('database.username'),
                    Config::get('database.password'),
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            } catch (PDOException $e) {
                throw new PDOException('Veritabanı bağlantısı kurulamadı: ' . $e->getMessage(), (int) $e->getCode());
            }
        }

        return self::$connection;
    }
}
