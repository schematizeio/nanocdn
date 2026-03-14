<?php
namespace NanoCDN;

spl_autoload_register(function (string $class): void {
    if (strpos($class, 'NanoCDN\\') !== 0) {
        return;
    }
    $name = substr($class, strlen('NanoCDN\\'));
    $file = __DIR__ . '/' . str_replace('\\', '/', $name) . '.php';
    if (is_file($file)) {
        require_once $file;
    }
});

function config(): array
{
    static $config;
    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }
    return $config;
}

function base_url(string $path = ''): string
{
    $cfg = config();
    $base = $cfg['base_url'] ?? '';
    if ($base === '' && isset($_SERVER['HTTP_HOST'])) {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $base .= str_replace('\\', '/', dirname($script));
        if (strpos($base, '/public') !== false) {
            $base = preg_replace('#/public$#', '', $base);
        }
    }
    $path = ltrim($path, '/');
    return $path !== '' ? rtrim($base, '/') . '/' . $path : rtrim($base, '/');
}

function storage_path(string $sub = ''): string
{
    $cfg = config();
    $base = $cfg['paths']['storage'] ?? __DIR__ . '/../storage';
    $sub = ltrim(str_replace('..', '', $sub), '/');
    return $sub !== '' ? $base . '/' . $sub : $base;
}

function uuid(): string
{
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function slug(string $s): string
{
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s);
    return strtolower(trim($s, '-'));
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function redirect(string $url, int $code = 302): void
{
    header('Location: ' . $url, true, $code);
    exit;
}
