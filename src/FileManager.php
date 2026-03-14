<?php
namespace NanoCDN;

class FileManager
{
    public static function createFromUpload(array $tenant, array $file): array
    {
        $tenantId = (int) $tenant['id'];
        $tenantUuid = $tenant['uuid'];
        $fileUuid = uuid();
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) ?: 'bin';
        $baseName = pathinfo($file['name'], PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-z0-9_-]/i', '-', $baseName);

        Tenant::ensureStorageDir($tenantUuid);
        $dir = storage_path($tenantUuid . '/' . $fileUuid);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $pathOriginal = $tenantUuid . '/' . $fileUuid . '/' . $safeName . '-original.' . $ext;
        $fullPath = storage_path($pathOriginal);
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \RuntimeException('Falha ao salvar arquivo');
        }

        $sizeBytes = (int) filesize($fullPath);
        $mime = $file['type'] ?? mime_content_type($fullPath);

        Database::run(
            'INSERT INTO files (tenant_id, file_uuid, original_name, mime_type, extension, size_bytes, path_original) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$tenantId, $fileUuid, $file['name'], $mime, $ext, $sizeBytes, $pathOriginal]
        );
        $fileId = (int) Database::lastInsertId();

        Database::run(
            'INSERT INTO file_variants (file_id, size_key, format, path, size_bytes) VALUES (?, ?, ?, ?, ?)',
            [$fileId, 'original', $ext, $pathOriginal, $sizeBytes]
        );

        $conversionEnabled = !empty($tenant['conversion_enabled']);
        $cfg = config();
        if ($conversionEnabled && !empty($cfg['conversion']['enabled']) && self::isImage($mime)) {
            ImageConverter::generateVariants($fileId, $fullPath, $tenantUuid, $fileUuid, $safeName, $ext);
        }

        return [
            'id' => $fileId,
            'file_uuid' => $fileUuid,
            'original_name' => $file['name'],
            'path_original' => $pathOriginal,
            'variants' => self::getVariants($fileId),
        ];
    }

    public static function isImage(string $mime): bool
    {
        return in_array($mime, [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif',
        ], true);
    }

    public static function getVariants(int $fileId): array
    {
        return Database::fetchAll('SELECT size_key, format, path, size_bytes FROM file_variants WHERE file_id = ? ORDER BY size_bytes DESC', [$fileId]);
    }

    public static function getByUuid(int $tenantId, string $fileUuid): ?array
    {
        $file = Database::fetchOne('SELECT * FROM files WHERE tenant_id = ? AND file_uuid = ?', [$tenantId, $fileUuid]);
        if ($file) {
            $file['variants'] = self::getVariants((int) $file['id']);
        }
        return $file;
    }

    public static function listByTenant(int $tenantId, int $limit = 100, int $offset = 0): array
    {
        $rows = Database::fetchAll(
            'SELECT id, file_uuid, original_name, mime_type, extension, size_bytes, path_original, created_at FROM files WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?',
            [$tenantId, $limit, $offset]
        );
        foreach ($rows as &$r) {
            $r['variants'] = self::getVariants((int) $r['id']);
        }
        return $rows;
    }

    public static function delete(int $fileId): bool
    {
        $file = Database::fetchOne('SELECT * FROM files WHERE id = ?', [$fileId]);
        if (!$file) {
            return false;
        }
        $variants = self::getVariants($fileId);
        foreach ($variants as $v) {
            $p = storage_path($v['path']);
            if (is_file($p)) {
                @unlink($p);
            }
        }
        $dir = storage_path(dirname($file['path_original']));
        if (is_dir($dir)) {
            @rmdir($dir);
        }
        Database::run('DELETE FROM files WHERE id = ?', [$fileId]);
        return true;
    }

    public static function getFilePath(string $pathRelative): ?string
    {
        $pathRelative = ltrim(str_replace('..', '', $pathRelative), '/');
        $full = storage_path($pathRelative);
        return is_file($full) ? $full : null;
    }
}
