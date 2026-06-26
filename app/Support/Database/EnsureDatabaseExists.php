<?php

namespace App\Support\Database;

use PDO;
use Throwable;

final class EnsureDatabaseExists
{
    public static function forDefaultConnection(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        $connection = config('database.connections.mysql');
        $database = (string) ($connection['database'] ?? '');

        if ($database === '') {
            return;
        }

        $host = (string) ($connection['host'] ?? '127.0.0.1');
        $port = (string) ($connection['port'] ?? '3306');
        $username = (string) ($connection['username'] ?? 'root');
        $password = (string) ($connection['password'] ?? '');
        $charset = (string) ($connection['charset'] ?? 'utf8mb4');
        $collation = (string) ($connection['collation'] ?? 'utf8mb4_unicode_ci');

        if (!preg_match('/^[A-Za-z0-9_]+$/', $database)) {
            return;
        }

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};charset={$charset}",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}");
        } catch (Throwable) {
            // Laravel will surface the original connection error during migrate.
        }
    }
}