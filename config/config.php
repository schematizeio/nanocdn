<?php
/**
 * NanoCDN - Configuração central
 * Ajuste estes valores ou use variáveis de ambiente em produção.
 */

$rootEnv = __DIR__ . '/../.env';
$legacyEnv = __DIR__ . '/.env.installed';
$envFile = is_file($rootEnv) ? $rootEnv : (is_file($legacyEnv) ? $legacyEnv : null);
if ($envFile) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($k, $v) = explode('=', $line, 2);
            putenv(trim($k) . '=' . trim($v, " \t\"'"));
        }
    }
}

return [
    'version' => '0.1.0',
    'env' => getenv('NANOCDN_ENV') ?: 'development',
    'debug' => filter_var(getenv('NANOCDN_DEBUG') ?: true, FILTER_VALIDATE_BOOLEAN),
    'base_url' => getenv('NANOCDN_BASE_URL') ?: (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') : '') . dirname($_SERVER['SCRIPT_NAME'] ?? ''),
    'timezone' => getenv('NANOCDN_TIMEZONE') ?: 'America/Sao_Paulo',

    'database' => [
        'host' => getenv('NANOCDN_DB_HOST') ?: 'localhost',
        'name' => getenv('NANOCDN_DB_NAME') ?: 'nanocdn',
        'user' => getenv('NANOCDN_DB_USER') ?: 'root',
        'pass' => getenv('NANOCDN_DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],

    'paths' => [
        'storage' => __DIR__ . '/../storage',
        'storage_public' => 'storage', // segmento URL para arquivos (ex: /storage)
    ],

    'conversion' => [
        'enabled' => false,
        'sizes' => [
            ['w' => 1920, 'h' => 1080],
            ['w' => 1366, 'h' => 1080],
            ['w' => 960, 'h' => 540],
            ['w' => 480, 'h' => 270],
            ['w' => 1080, 'h' => 1080],
            ['w' => 512, 'h' => 512],
        ],
        'formats' => ['png', 'webp', 'avif'],
        'driver' => 'auto', // auto | gd | imagick
    ],

    'upload' => [
        'max_size_mb' => 50,
        'allowed_mimes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'],
    ],

    'cors' => [
        'enabled' => filter_var(getenv('NANOCDN_CORS') ?: false, FILTER_VALIDATE_BOOLEAN),
        'allowed_origins' => ['*'],
    ],
];
