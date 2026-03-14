<?php
/**
 * NanoCDN - API S3-compatible (PutObject, GetObject, HeadObject, DeleteObject, ListObjectsV2)
 * Bucket = tenant slug ou uuid. Autenticação: header API-Key (mesmo da API REST).
 */

use NanoCDN\FileManager;
use NanoCDN\storage_path;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$bucket = $s3Bucket ?? '';
$key = $s3Key ?? '';
$tenant = $s3Tenant ?? null;
$cfg = $s3Cfg ?? [];

if (!$tenant || ($bucket !== $tenant['slug'] && $bucket !== $tenant['uuid'])) {
    s3_error_xml(403, 'AccessDenied', 'Bucket does not match API key tenant');
    exit;
}

$tenantId = (int) $tenant['id'];
$maxSize = ($cfg['upload']['max_size_mb'] ?? 50) * 1024 * 1024;

// ListObjectsV2: GET /api/s3/{bucket}?list-type=2&prefix=&max-keys=&continuation-token=
if ($method === 'GET' && $key === '') {
    $prefix = $_GET['prefix'] ?? '';
    $maxKeys = min(1000, max(1, (int) ($_GET['max-keys'] ?? 1000)));
    $continuationToken = $_GET['continuation-token'] ?? '';
    $offset = is_numeric($continuationToken) ? (int) $continuationToken : 0;
    $result = FileManager::listByTenantS3Prefix($tenantId, $prefix, $maxKeys, $offset);
    s3_list_objects_xml($result, $bucket, $prefix, $maxKeys, $result['NextOffset']);
    exit;
}

// PutObject: PUT /api/s3/{bucket}/{key}
if ($method === 'PUT' && $key !== '') {
    $contentType = $_SERVER['HTTP_CONTENT_TYPE'] ?? $_SERVER['CONTENT_TYPE'] ?? 'application/octet-stream';
    $tmp = tempnam(sys_get_temp_dir(), 'nc_');
    try {
        $input = fopen('php://input', 'rb');
        $written = 0;
        $fp = fopen($tmp, 'wb');
        while (!feof($input)) {
            $chunk = fread($input, 65536);
            if ($chunk === false) {
                break;
            }
            $written += strlen($chunk);
            if ($written > $maxSize) {
                fclose($fp);
                fclose($input);
                unlink($tmp);
                s3_error_xml(400, 'EntityTooLarge', 'Object size exceeds max');
                exit;
            }
            fwrite($fp, $chunk);
        }
        fclose($fp);
        fclose($input);
        $result = FileManager::createFromS3Put($tenant, $key, $tmp, $contentType);
        unlink($tmp);
        $row = \NanoCDN\Database::fetchOne('SELECT created_at FROM files WHERE id = ?', [$result['id']]);
        $etag = '"' . md5($result['path_original'] . ($row['created_at'] ?? '')) . '"';
        http_response_code(200);
        header('ETag: ' . $etag);
        header('Content-Length: 0');
        exit;
    } catch (\Throwable $e) {
        if (is_file($tmp)) {
            @unlink($tmp);
        }
        s3_error_xml(500, 'InternalError', $e->getMessage());
        exit;
    }
}

// GetObject: GET /api/s3/{bucket}/{key}
if ($method === 'GET' && $key !== '') {
    $file = FileManager::getByS3Key($tenantId, $key);
    if (!$file) {
        s3_error_xml(404, 'NoSuchKey', 'The specified key does not exist.');
        exit;
    }
    $fullPath = storage_path($file['path_original']);
    if (!is_file($fullPath)) {
        s3_error_xml(404, 'NoSuchKey', 'Object not found on storage.');
        exit;
    }
    $size = filesize($fullPath);
    $mtime = filemtime($fullPath);
    $etag = '"' . md5($fullPath . $mtime . $size) . '"';
    header('Content-Type: ' . ($file['mime_type'] ?? 'application/octet-stream'));
    header('Content-Length: ' . $size);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    header('ETag: ' . $etag);
    header('Accept-Ranges: bytes');
    readfile($fullPath);
    exit;
}

// HeadObject: HEAD /api/s3/{bucket}/{key}
if ($method === 'HEAD' && $key !== '') {
    $file = FileManager::getByS3Key($tenantId, $key);
    if (!$file) {
        http_response_code(404);
        header('Content-Type: application/xml');
        echo '<?xml version="1.0" encoding="UTF-8"?><Error><Code>NoSuchKey</Code><Message>The specified key does not exist.</Message></Error>';
        exit;
    }
    $fullPath = storage_path($file['path_original']);
    if (!is_file($fullPath)) {
        http_response_code(404);
        exit;
    }
    $size = filesize($fullPath);
    $mtime = filemtime($fullPath);
    $etag = '"' . md5($fullPath . $mtime . $size) . '"';
    header('Content-Type: ' . ($file['mime_type'] ?? 'application/octet-stream'));
    header('Content-Length: ' . $size);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    header('ETag: ' . $etag);
    header('Accept-Ranges: bytes');
    http_response_code(200);
    exit;
}

// DeleteObject: DELETE /api/s3/{bucket}/{key}
if ($method === 'DELETE' && $key !== '') {
    $deleted = FileManager::deleteByS3Key($tenantId, $key);
    http_response_code($deleted ? 204 : 404);
    if (!$deleted) {
        s3_error_xml(404, 'NoSuchKey', 'The specified key does not exist.');
    }
    exit;
}

s3_error_xml(400, 'InvalidRequest', 'Unsupported method or path');

function s3_error_xml(int $httpCode, string $code, string $message): void
{
    http_response_code($httpCode);
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<Error><Code>' . htmlspecialchars($code) . '</Code><Message>' . htmlspecialchars($message) . '</Message></Error>';
}

function s3_list_objects_xml(array $result, string $bucket, string $prefix, int $maxKeys, $nextOffset): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<ListBucketResult xmlns="http://s3.amazonaws.com/doc/2006-03-01/">' . "\n";
    $xml .= '  <Name>' . htmlspecialchars($bucket) . '</Name>' . "\n";
    $xml .= '  <Prefix>' . htmlspecialchars($prefix) . '</Prefix>' . "\n";
    $xml .= '  <KeyCount>' . count($result['Contents']) . '</KeyCount>' . "\n";
    $xml .= '  <MaxKeys>' . $maxKeys . '</MaxKeys>' . "\n";
    $xml .= '  <IsTruncated>' . ($result['IsTruncated'] ? 'true' : 'false') . '</IsTruncated>' . "\n";
    if ($result['IsTruncated'] && $nextOffset !== null) {
        $xml .= '  <NextContinuationToken>' . (int) $nextOffset . '</NextContinuationToken>' . "\n";
    }
    foreach ($result['Contents'] as $c) {
        $xml .= '  <Contents>' . "\n";
        $xml .= '    <Key>' . htmlspecialchars($c['Key']) . '</Key>' . "\n";
        $xml .= '    <Size>' . (int) $c['Size'] . '</Size>' . "\n";
        $xml .= '    <LastModified>' . htmlspecialchars($c['LastModified']) . '</LastModified>' . "\n";
        $xml .= '    <ETag>' . htmlspecialchars($c['ETag']) . '</ETag>' . "\n";
        $xml .= '  </Contents>' . "\n";
    }
    $xml .= '</ListBucketResult>';
    echo $xml;
}
