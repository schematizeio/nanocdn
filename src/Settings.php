<?php
namespace NanoCDN;

class Settings
{
    public static function get(string $key): ?string
    {
        $row = Database::fetchOne('SELECT `value` FROM settings WHERE `key` = ?', [$key]);
        return $row ? $row['value'] : null;
    }

    public static function set(string $key, ?string $value): void
    {
        if ($value === null) {
            Database::run('DELETE FROM settings WHERE `key` = ?', [$key]);
            return;
        }
        Database::run(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value]
        );
    }
}
