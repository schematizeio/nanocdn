<?php
/**
 * NanoCDN - Front controller
 */

define('NANOCDN_ROOT', dirname(__DIR__));
chdir(NANOCDN_ROOT);

require NANOCDN_ROOT . '/src/helpers.php';
require_once NANOCDN_ROOT . '/src/Database.php';

$config = \NanoCDN\config();
if (!empty($config['timezone'])) {
    date_default_timezone_set($config['timezone']);
}

$path = $_GET['__path'] ?? '/';
$path = '/' . trim($path, '/');
if ($path === '/') {
    $path = '/admin';
}

$envInstalled = is_file(NANOCDN_ROOT . '/config/.env.installed');
if (!$envInstalled && $path !== '/install.php') {
    $isApiHealth = (strpos($path, '/api/') === 0 && preg_match('#^/api/health$#', $path));
    $isFileDelivery = (strpos($path, '/f/') === 0);
    if (!$isApiHealth && !$isFileDelivery) {
        \NanoCDN\redirect(\NanoCDN\base_url('install.php'));
    }
}

\NanoCDN\Database::init($config['database']);

// Rotas estáticas: servir arquivo do storage
if (preg_match('#^/f/([a-f0-9-]+)/([a-f0-9-]+)/([^/]+)$#', $path, $m)) {
    $tenantUuid = $m[1];
    $fileUuid = $m[2];
    $filename = $m[3];
    if (strpos($filename, '..') !== false) {
        http_response_code(404);
        exit;
    }
    $relative = $tenantUuid . '/' . $fileUuid . '/' . $filename;
    $full = \NanoCDN\storage_path($relative);
    $storageRoot = rtrim(\NanoCDN\storage_path(''), '/');
    $realFull = realpath($full);
    $realRoot = realpath($storageRoot);
    if ($realFull === false || $realRoot === false || strpos($realFull, $realRoot) !== 0 || !is_file($realFull)) {
        http_response_code(404);
        exit;
    }
    $mtime = filemtime($realFull);
    $size = filesize($realFull);
    $etag = '"' . md5($realFull . $mtime . $size) . '"';
    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
        http_response_code(304);
        exit;
    }
    if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
        $ifModified = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
        if ($ifModified !== false && $mtime <= $ifModified) {
            http_response_code(304);
            exit;
        }
    }
    header('Content-Type: ' . mime_content_type($realFull));
    header('Cache-Control: public, max-age=31536000');
    header('Content-Length: ' . $size);
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');
    header('ETag: ' . $etag);
    header('X-Content-Type-Options: nosniff');
    readfile($realFull);
    exit;
}

// API
if (strpos($path, '/api/') === 0) {
    require NANOCDN_ROOT . '/public/api.php';
    exit;
}

// Admin
if (strpos($path, '/admin') === 0) {
    require NANOCDN_ROOT . '/public/admin.php';
    exit;
}

http_response_code(404);
require NANOCDN_ROOT . '/public/views/404.php';
