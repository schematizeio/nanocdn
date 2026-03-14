<?php
/**
 * NanoCDN - API (upload, list, delete)
 * Autenticação: header API-Key: nc_...
 */

use NanoCDN\config;
use NanoCDN\FileManager;
use NanoCDN\json_response;
use NanoCDN\Tenant;

$cfg = config();
if (!empty($cfg['cors']['enabled'])) {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = $cfg['cors']['allowed_origins'] ?? ['*'];
    if (in_array('*', $allowed, true) || in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    }
    header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: API-Key, X-Api-Key, Content-Type');
    header('Access-Control-Max-Age: 86400');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

$path = $_GET['__path'] ?? '';
$path = trim($path, '/');
$path = preg_replace('#^api/#', '', $path);
$parts = $path ? explode('/', $path) : [];
$resource = $parts[0] ?? '';
$id = $parts[1] ?? '';

// Endpoint de saúde (sem autenticação)
if ($resource === 'health' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        \NanoCDN\Database::get()->query('SELECT 1');
        json_response([
            'status' => 'ok',
            'database' => 'connected',
            'version' => $cfg['version'] ?? '0.1.0',
        ]);
    } catch (\Throwable $e) {
        json_response([
            'status' => 'error',
            'database' => 'disconnected',
            'version' => $cfg['version'] ?? '0.1.0',
        ], 503);
    }
    exit;
}

$apiKey = $_SERVER['HTTP_API_KEY'] ?? $_SERVER['HTTP_X_API_KEY'] ?? '';
if ($apiKey === '') {
    json_response(['error' => 'Missing API-Key header', 'code' => 401], 401);
    exit;
}

$tenant = Tenant::getByApiKey($apiKey);
if (!$tenant) {
    json_response(['error' => 'Invalid API key', 'code' => 403], 403);
    exit;
}

$maxSize = ($cfg['upload']['max_size_mb'] ?? 50) * 1024 * 1024;
$allowedMimes = $cfg['upload']['allowed_mimes'] ?? [];

if ($resource === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['file'] ?? null;
    if (!$file || ($file['error'] !== UPLOAD_ERR_OK && $file['error'] !== 0)) {
        $code = $file['error'] ?? 'no_file';
        json_response(['error' => 'Upload failed', 'upload_error' => $code], 400);
        exit;
    }
    if ($file['size'] > $maxSize) {
        json_response(['error' => 'File too large', 'max_mb' => $cfg['upload']['max_size_mb']], 400);
        exit;
    }
    if (!empty($allowedMimes) && !in_array($file['type'], $allowedMimes)) {
        json_response(['error' => 'File type not allowed', 'allowed' => $allowedMimes], 400);
        exit;
    }
    $realMime = null;
    if (function_exists('finfo_open') && is_uploaded_file($file['tmp_name'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
        if ($finfo) {
            finfo_close($finfo);
        }
    }
    if ($realMime !== null && !empty($allowedMimes) && !in_array($realMime, $allowedMimes)) {
        json_response(['error' => 'File type not allowed (content check)', 'allowed' => $allowedMimes], 400);
        exit;
    }
    try {
        $result = FileManager::createFromUpload($tenant, $file);
        $baseUrl = \NanoCDN\base_url();
        foreach ($result['variants'] as &$v) {
            $v['url'] = $baseUrl . '/f/' . $tenant['uuid'] . '/' . $result['file_uuid'] . '/' . basename($v['path']);
        }
        unset($v);
        json_response([
            'ok' => true,
            'file_uuid' => $result['file_uuid'],
            'original_name' => $result['original_name'],
            'variants' => $result['variants'],
        ], 201);
    } catch (\Throwable $e) {
        json_response(['error' => 'Server error', 'message' => $e->getMessage()], 500);
    }
    exit;
}

if ($resource === 'files' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $tenantId = (int) $tenant['id'];
    $limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
    $offset = max(0, (int) ($_GET['offset'] ?? 0));
    $total = (int) \NanoCDN\Database::fetchOne('SELECT COUNT(*) AS n FROM files WHERE tenant_id = ?', [$tenantId])['n'];
    $list = FileManager::listByTenant($tenantId, $limit, $offset);
    $baseUrl = \NanoCDN\base_url();
    foreach ($list as &$f) {
        $f['urls'] = [];
        foreach ($f['variants'] as $v) {
            $f['urls'][$v['size_key'] . '.' . $v['format']] = $baseUrl . '/f/' . $tenant['uuid'] . '/' . $f['file_uuid'] . '/' . basename($v['path']);
        }
    }
    unset($f);
    json_response(['files' => $list, 'total' => $total, 'limit' => $limit, 'offset' => $offset]);
    exit;
}

if ($resource === 'files' && $id !== '' && $_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $tenantId = (int) $tenant['id'];
    $file = \NanoCDN\Database::fetchOne('SELECT id FROM files WHERE tenant_id = ? AND file_uuid = ?', [$tenantId, $id]);
    if (!$file) {
        json_response(['error' => 'File not found'], 404);
        exit;
    }
    FileManager::delete((int) $file['id']);
    json_response(['ok' => true]);
    exit;
}

if ($resource === 'files' && $id !== '' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $tenantId = (int) $tenant['id'];
    $file = FileManager::getByUuid($tenantId, $id);
    if (!$file) {
        json_response(['error' => 'File not found'], 404);
        exit;
    }
    $baseUrl = \NanoCDN\base_url();
    foreach ($file['variants'] as &$v) {
        $v['url'] = $baseUrl . '/f/' . $tenant['uuid'] . '/' . $file['file_uuid'] . '/' . basename($v['path']);
    }
    unset($v);
    json_response($file);
    exit;
}

json_response(['error' => 'Not found', 'code' => 404], 404);
