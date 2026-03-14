<?php
namespace NanoCDN;

class Tenant
{
    public static function all(bool $activeOnly = false): array
    {
        $sql = 'SELECT t.*, (SELECT COUNT(*) FROM files f WHERE f.tenant_id = t.id) AS file_count
                FROM tenants t';
        if ($activeOnly) {
            $sql .= ' WHERE t.active = 1';
        }
        $sql .= ' ORDER BY t.name';
        return Database::fetchAll($sql);
    }

    public static function getById(int $id): ?array
    {
        return Database::fetchOne('SELECT * FROM tenants WHERE id = ?', [$id]);
    }

    /** @param string $uuid UUID do tenant (usado em URLs). Admin pode ver inativos. */
    public static function getByUuid(string $uuid): ?array
    {
        return Database::fetchOne('SELECT * FROM tenants WHERE uuid = ?', [$uuid]);
    }

    public static function getByApiKey(string $apiKey): ?array
    {
        $keyHash = hash('sha256', $apiKey);
        $row = Database::fetchOne(
            'SELECT t.* FROM tenants t
             INNER JOIN api_keys k ON k.tenant_id = t.id
             WHERE k.key_hash = ? AND t.active = 1',
            [$keyHash]
        );
        if ($row) {
            Database::run(
                'UPDATE api_keys SET last_used_at = NOW() WHERE key_hash = ?',
                [$keyHash]
            );
        }
        return $row;
    }

    public static function create(string $name, bool $conversionEnabled = false): array
    {
        $uuid = uuid();
        $slug = slug($name);
        $i = 0;
        while (Database::fetchOne('SELECT id FROM tenants WHERE slug = ?', [$slug])) {
            $slug = slug($name) . '-' . (++$i);
        }
        Database::run(
            'INSERT INTO tenants (uuid, name, slug, active, conversion_enabled) VALUES (?, ?, ?, 1, ?)',
            [$uuid, $name, $slug, $conversionEnabled ? 1 : 0]
        );
        return self::getById((int) Database::lastInsertId());
    }

    public static function update(int $id, array $data): bool
    {
        $allowed = ['name', 'slug', 'active', 'conversion_enabled'];
        $set = [];
        $params = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $data)) {
                $set[] = "`$k` = ?";
                $params[] = $data[$k];
            }
        }
        if (empty($set)) {
            return true;
        }
        $params[] = $id;
        Database::run('UPDATE tenants SET ' . implode(', ', $set) . ' WHERE id = ?', $params);
        return true;
    }

    public static function createApiKey(int $tenantId, string $name = 'Default'): array
    {
        $raw = 'nc_' . bin2hex(random_bytes(24));
        $keyHash = hash('sha256', $raw);
        $keyPrefix = substr($raw, 0, 8);
        Database::run(
            'INSERT INTO api_keys (tenant_id, key_hash, key_prefix, name) VALUES (?, ?, ?, ?)',
            [$tenantId, $keyHash, $keyPrefix, $name]
        );
        return [
            'id' => (int) Database::lastInsertId(),
            'key' => $raw,
            'key_prefix' => $keyPrefix . '...',
        ];
    }

    public static function getApiKeys(int $tenantId): array
    {
        return Database::fetchAll(
            'SELECT id, key_prefix, name, last_used_at, created_at FROM api_keys WHERE tenant_id = ? ORDER BY created_at DESC',
            [$tenantId]
        );
    }

    public static function revokeApiKey(int $keyId, int $tenantId): bool
    {
        $n = Database::run('DELETE FROM api_keys WHERE id = ? AND tenant_id = ?', [$keyId, $tenantId])->rowCount();
        return $n > 0;
    }

    public static function ensureStorageDir(string $tenantUuid): string
    {
        $dir = storage_path($tenantUuid);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }
}
