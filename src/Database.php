<?php
namespace NanoCDN;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
        self::$pdo = null;
    }

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $c['host'],
                $c['name'],
                $c['charset'] ?? 'utf8mb4'
            );
            self::$pdo = new PDO($dsn, $c['user'], $c['pass'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::get()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::run($sql, $params);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    public static function lastInsertId(): string
    {
        return (string) self::get()->lastInsertId();
    }
}
