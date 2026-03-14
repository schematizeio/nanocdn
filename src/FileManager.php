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
        if ($conversionEnabled && self::isImage($mime)) {
            ImageConverter::generateVariants($fileId, $fullPath, $tenantUuid, $fileUuid, $safeName, $ext, $tenant);
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
        $limit = max(1, min(1000, $limit));
        $offset = max(0, $offset);
        $rows = Database::fetchAll(
            'SELECT id, file_uuid, original_name, mime_type, extension, size_bytes, path_original, created_at FROM files WHERE tenant_id = ? ORDER BY created_at DESC LIMIT ' . $limit . ' OFFSET ' . $offset,
            [$tenantId]
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

    /** Cria ou substitui arquivo via API S3 (PutObject). $bodyPath = caminho do arquivo temporário com o corpo. */
    public static function createFromS3Put(array $tenant, string $s3Key, string $bodyPath, string $contentType): array
    {
        $tenantId = (int) $tenant['id'];
        $tenantUuid = $tenant['uuid'];
        $existing = self::getByS3Key($tenantId, $s3Key);
        if ($existing) {
            self::delete((int) $existing['id']);
        }
        $fileUuid = uuid();
        $ext = strtolower(pathinfo($s3Key, PATHINFO_EXTENSION)) ?: 'bin';
        $baseName = pathinfo($s3Key, PATHINFO_FILENAME);
        $safeName = preg_replace('/[^a-z0-9_-]/i', '-', $baseName);

        Tenant::ensureStorageDir($tenantUuid);
        $dir = storage_path($tenantUuid . '/' . $fileUuid);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $pathOriginal = $tenantUuid . '/' . $fileUuid . '/' . $safeName . '-original.' . $ext;
        $fullPath = storage_path($pathOriginal);
        if (!copy($bodyPath, $fullPath)) {
            throw new \RuntimeException('Falha ao salvar arquivo');
        }
        $sizeBytes = (int) filesize($fullPath);
        $mime = $contentType ?: mime_content_type($fullPath);
        $originalName = basename($s3Key);

        Database::run(
            'INSERT INTO files (tenant_id, file_uuid, original_name, mime_type, extension, size_bytes, path_original, s3_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$tenantId, $fileUuid, $originalName, $mime, $ext, $sizeBytes, $pathOriginal, $s3Key]
        );
        $fileId = (int) Database::lastInsertId();
        Database::run(
            'INSERT INTO file_variants (file_id, size_key, format, path, size_bytes) VALUES (?, ?, ?, ?, ?)',
            [$fileId, 'original', $ext, $pathOriginal, $sizeBytes]
        );
        $conversionEnabled = !empty($tenant['conversion_enabled']);
        if ($conversionEnabled && self::isImage($mime)) {
            ImageConverter::generateVariants($fileId, $fullPath, $tenantUuid, $fileUuid, $safeName, $ext, $tenant);
        }
        return [
            'id' => $fileId,
            'file_uuid' => $fileUuid,
            'original_name' => $originalName,
            'path_original' => $pathOriginal,
            's3_key' => $s3Key,
            'variants' => self::getVariants($fileId),
        ];
    }

    public static function getByS3Key(int $tenantId, string $s3Key): ?array
    {
        $file = Database::fetchOne('SELECT * FROM files WHERE tenant_id = ? AND s3_key = ?', [$tenantId, $s3Key]);
        if ($file) {
            $file['variants'] = self::getVariants((int) $file['id']);
        }
        return $file;
    }

    /** Lista arquivos do tenant com s3_key definido, opcionalmente filtrado por prefix. Retorna array de {Key, Size, LastModified, ETag}. */
    public static function listByTenantS3Prefix(int $tenantId, string $prefix = '', int $maxKeys = 1000, int $offset = 0): array
    {
        $maxKeys = max(1, min(1000, $maxKeys));
        $sql = 'SELECT id, file_uuid, s3_key, size_bytes, path_original, created_at FROM files WHERE tenant_id = ? AND s3_key IS NOT NULL';
        $params = [$tenantId];
        if ($prefix !== '') {
            $sql .= ' AND s3_key LIKE ?';
            $params[] = $prefix . '%';
        }
        $sql .= ' ORDER BY s3_key ASC LIMIT ' . ($maxKeys + 1) . ' OFFSET ' . $offset;
        $rows = Database::fetchAll($sql, $params);
        $hasMore = count($rows) > $maxKeys;
        if ($hasMore) {
            array_pop($rows);
        }
        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'Key' => $r['s3_key'],
                'Size' => (int) $r['size_bytes'],
                'LastModified' => date('Y-m-d\TH:i:s.000\Z', strtotime($r['created_at'])),
                'ETag' => '"' . md5($r['path_original'] . $r['created_at']) . '"',
            ];
        }
        return ['Contents' => $list, 'IsTruncated' => $hasMore, 'NextOffset' => $hasMore ? $offset + $maxKeys : null];
    }

    public static function deleteByS3Key(int $tenantId, string $s3Key): bool
    {
        $file = Database::fetchOne('SELECT id FROM files WHERE tenant_id = ? AND s3_key = ?', [$tenantId, $s3Key]);
        if (!$file) {
            return false;
        }
        return self::delete((int) $file['id']);
    }
}
