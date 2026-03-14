<?php
/**
 * NanoCDN - Front controller
 */

$indexDir = __DIR__;
define('NANOCDN_ROOT', (basename($indexDir) === 'public' ? dirname($indexDir) : $indexDir));
chdir(NANOCDN_ROOT);

$helpersPath = NANOCDN_ROOT . '/src/helpers.php';
if (!is_file($helpersPath)) {
    http_response_code(500);
    die('NanoCDN: src/helpers.php não encontrado. Raiz esperada: ' . NANOCDN_ROOT);
}
require $helpersPath;

$databasePath = NANOCDN_ROOT . '/src/Database.php';
if (!is_file($databasePath)) {
    http_response_code(500);
    die('NanoCDN: src/Database.php não encontrado. Raiz: ' . NANOCDN_ROOT);
}
require_once $databasePath;
if (!class_exists('NanoCDN\Database', false)) {
    http_response_code(500);
    die('NanoCDN: classe Database não encontrada após carregar ' . $databasePath);
}

$config = \NanoCDN\config();
if (!empty($config['timezone'])) {
    date_default_timezone_set($config['timezone']);
}

$path = $_GET['__path'] ?? '/';
$path = '/' . trim($path, '/');
if ($path === '/') {
    $path = '/admin';
}

$envInstalled = is_file(NANOCDN_ROOT . '/.env') || is_file(NANOCDN_ROOT . '/config/.env.installed');
if (!$envInstalled && $path !== '/install.php') {
    $isApiHealth = (strpos($path, '/api/') === 0 && preg_match('#^/api/health$#', $path));
    $isFileDelivery = (strpos($path, '/f/') === 0);
    if (!$isApiHealth && !$isFileDelivery) {
        \NanoCDN\redirect(\NanoCDN\base_url('install.php'));
    }
}

\NanoCDN\Database::init($config['database']);

// Arquivos estáticos do admin (CSS/JS na pasta public)
$publicDir = __DIR__;
$staticPath = $publicDir . preg_replace('#/+#', '/', $path);
if (preg_match('#^/[a-z0-9_.-]+\.(css|js|ico)$#i', $path) && is_file($staticPath) && strpos(realpath($staticPath), realpath($publicDir)) === 0) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimes = ['css' => 'text/css', 'js' => 'application/javascript', 'ico' => 'image/x-icon'];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Cache-Control: public, max-age=3600');
        readfile($staticPath);
        exit;
    }
}

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
