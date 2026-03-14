<?php
/**
 * NanoCDN - Admin (login, tenants, files, checker)
 */

use NanoCDN\Auth;
use NanoCDN\base_url;
use NanoCDN\Database;
use NanoCDN\FileManager;
use NanoCDN\ImageConverter;
use NanoCDN\redirect;
use NanoCDN\Tenant;

header('X-Content-Type-Options: nosniff');

$path = $_GET['__path'] ?? '/admin';
$path = preg_replace('#^/admin#', '', $path) ?: '/';
$path = '/' . trim($path, '/');

if ($path === '/login') {
    if (Auth::check()) {
        \NanoCDN\redirect(\NanoCDN\base_url('admin'));
    }
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Auth::csrfVerify()) {
            $error = 'Requisição inválida. Tente novamente.';
        } else {
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            if (Auth::login($email, $pass)) {
                \NanoCDN\redirect(\NanoCDN\base_url('admin'));
            }
            $error = 'E-mail ou senha inválidos.';
        }
    }
    require __DIR__ . '/views/admin_login.php';
    exit;
}

if ($path === '/logout') {
    Auth::logout();
    \NanoCDN\redirect(\NanoCDN\base_url('admin/login'));
}

Auth::requireAdmin();

if ($path === '/check') {
    $caps = ImageConverter::getServerCapabilities();
    $dbOk = false;
    try {
        Database::get()->query('SELECT 1');
        $dbOk = true;
    } catch (\Throwable $e) {
        // ignore
    }
    $storagePath = \NanoCDN\storage_path('');
    $storageWritable = is_dir($storagePath) && is_writable($storagePath);
    $phpVersion = PHP_VERSION;
    $appVersion = (\NanoCDN\config())['version'] ?? '0.1.0';
    require __DIR__ . '/views/admin_check.php';
    exit;
}

if ($path === '/update') {
    $output = '';
    $error = '';
    $manualUpdateCommand = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $repoRoot = defined('NANOCDN_ROOT') ? NANOCDN_ROOT : dirname(__DIR__);
        if (!is_dir($repoRoot . '/.git')) {
            $error = 'Este diretório não é um clone Git. Faça o deploy via clone (git clone) para usar atualização automática.';
        } else {
            $branch = 'master';
            $safeRoot = function_exists('escapeshellarg') ? escapeshellarg($repoRoot) : "'" . str_replace("'", "'\\''", $repoRoot) . "'";
            $safeBranch = function_exists('escapeshellarg') ? escapeshellarg($branch) : "'" . str_replace("'", "'\\''", $branch) . "'";
            $manualUpdateCommand = 'cd ' . $repoRoot . ' && git pull origin main';
            if (function_exists('exec')) {
                $cmd = "cd {$safeRoot} && git pull origin {$safeBranch} 2>&1";
                exec($cmd, $lines, $code);
                $output = implode("\n", $lines ?: []);
                if ($code !== 0) {
                    $safeMain = function_exists('escapeshellarg') ? escapeshellarg('main') : 'main';
                    $cmdMain = "cd {$safeRoot} && git pull origin {$safeMain} 2>&1";
                    exec($cmdMain, $linesMain, $codeMain);
                    $output = implode("\n", $linesMain ?: []);
                    if ($codeMain === 0) {
                        $output = "Atualização concluída (branch main).\n" . $output;
                    } else {
                        $error = 'Falha no git pull. Execute no servidor (SSH ou terminal):';
                        $manualUpdateCommand = 'cd ' . $repoRoot . ' && git pull origin main';
                        $output = trim($output) ?: 'Sem saída.';
                    }
                } else {
                    $output = "Atualização concluída (branch {$branch}).\n" . $output;
                }
            } else {
                $error = 'exec() desabilitada neste servidor. Execute no servidor (SSH ou terminal):';
            }
        }
    }
    require __DIR__ . '/views/admin_update.php';
    exit;
}

if ($path === '/password') {
    $error = '';
    $success = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($new !== $confirm) {
            $error = 'A nova senha e a confirmação não coincidem.';
        } else {
            $err = Auth::changePassword((int) Auth::user()['id'], $current, $new);
            if ($err) {
                $error = $err;
            } else {
                $success = 'Senha alterada com sucesso.';
            }
        }
    }
    require __DIR__ . '/views/admin_password.php';
    exit;
}

if ($path === '/review' || $path === '/revisao') {
    $cfg = \NanoCDN\config();
    $review = [
        'php_version' => PHP_VERSION,
        'app_version' => $cfg['version'] ?? '0.1.0',
        'env' => $cfg['env'] ?? 'development',
        'base_url' => \NanoCDN\base_url(),
        'storage_path' => \NanoCDN\storage_path(''),
        'db_ok' => false,
        'storage_writable' => false,
        'storage_free' => null,
        'caps' => \NanoCDN\ImageConverter::getServerCapabilities(),
        'tenants_count' => 0,
        'files_count' => 0,
        'api_keys_count' => 0,
        'https' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
    ];
    try {
        Database::get()->query('SELECT 1');
        $review['db_ok'] = true;
        $review['tenants_count'] = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM tenants')['n'];
        $review['files_count'] = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM files')['n'];
        $review['api_keys_count'] = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM api_keys')['n'];
    } catch (\Throwable $e) {
        $review['db_error'] = $e->getMessage();
    }
    $review['storage_writable'] = is_dir($review['storage_path']) && is_writable($review['storage_path']);
    if (function_exists('disk_free_space') && @disk_free_space($review['storage_path']) !== false) {
        $review['storage_free'] = round(disk_free_space($review['storage_path']) / (1024 * 1024), 1);
    }
    require __DIR__ . '/views/admin_review.php';
    exit;
}

if ($path === '/' || $path === '') {
    $tenants = Tenant::all();
    $totalFiles = Database::fetchOne('SELECT COUNT(*) AS n FROM files');
    require __DIR__ . '/views/admin_dashboard.php';
    exit;
}

// UUID do tenant: 8-4-4-4-12 hex
$uuidRegex = '[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}';

if (preg_match('#^/tenants/new$#', $path)) {
    $globalConversion = ImageConverter::getGlobalConversionOptions();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $name = trim($_POST['name'] ?? '');
        $conversion = !empty($_POST['conversion_enabled']);
        $sizes = is_array($_POST['conversion_sizes'] ?? null) ? $_POST['conversion_sizes'] : [];
        $formats = is_array($_POST['conversion_formats'] ?? null) ? $_POST['conversion_formats'] : [];
        if ($name !== '') {
            $t = Tenant::create($name, $conversion, $sizes ?: null, $formats ?: null);
            $keyData = Tenant::createApiKey($t['id'], 'Default');
            \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . $t['uuid'] . '?new_key=' . urlencode($keyData['key'])));
        }
    }
    require __DIR__ . '/views/tenant_form.php';
    exit;
}

if (preg_match('#^/tenants/(' . $uuidRegex . ')$#', $path, $m)) {
    $tenantUuid = $m[1];
    $tenant = Tenant::getByUuid($tenantUuid);
    if (!$tenant) {
        header('Location: ' . \NanoCDN\base_url('admin'));
        exit;
    }
    $tenantId = (int) $tenant['id'];
    $newKey = $_GET['new_key'] ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'regenerate_key') {
                Tenant::createApiKey($tenantId, 'Generated ' . date('Y-m-d H:i'));
                \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . $tenantUuid));
            }
            if ($_POST['action'] === 'update') {
                $upSizes = is_array($_POST['conversion_sizes'] ?? null) ? $_POST['conversion_sizes'] : [];
                $upFormats = is_array($_POST['conversion_formats'] ?? null) ? $_POST['conversion_formats'] : [];
                $encoded = Tenant::validateAndEncodeConversionOptions($upSizes, $upFormats);
                Tenant::update($tenantId, [
                    'name' => trim($_POST['name'] ?? ''),
                    'active' => isset($_POST['active']) ? 1 : 0,
                    'conversion_enabled' => isset($_POST['conversion_enabled']) ? 1 : 0,
                    'conversion_sizes' => $encoded['sizes'],
                    'conversion_formats' => $encoded['formats'],
                ]);
                \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . $tenantUuid));
            }
        }
    }
    $globalConversion = ImageConverter::getGlobalConversionOptions();
    $tenantConversionSizes = $tenant['conversion_sizes'] ? (json_decode($tenant['conversion_sizes'], true) ?: []) : [];
    $tenantConversionFormats = $tenant['conversion_formats'] ? (json_decode($tenant['conversion_formats'], true) ?: []) : [];
    $apiKeys = Tenant::getApiKeys($tenantId);
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 20;
    $offset = ($page - 1) * $perPage;
    $files = FileManager::listByTenant($tenantId, $perPage, $offset);
    $totalFiles = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM files WHERE tenant_id = ?', [$tenantId])['n'];
    $totalPages = $totalFiles ? (int) ceil($totalFiles / $perPage) : 1;
    require __DIR__ . '/views/tenant_detail.php';
    exit;
}

if (preg_match('#^/tenants/(' . $uuidRegex . ')/upload$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $tenant = Tenant::getByUuid($m[1]);
    if (!$tenant) {
        \NanoCDN\redirect(\NanoCDN\base_url('admin'));
    }
    $cfg = \NanoCDN\config();
    $maxSize = ($cfg['upload']['max_size_mb'] ?? 50) * 1024 * 1024;
    $allowedMimes = $cfg['upload']['allowed_mimes'] ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
    $file = $_FILES['file'] ?? null;
    $uploadError = null;
    if ($file && ($file['error'] === UPLOAD_ERR_OK || $file['error'] === 0)) {
        if ($file['size'] > $maxSize) {
            $uploadError = 'Arquivo maior que ' . ($cfg['upload']['max_size_mb'] ?? 50) . ' MB.';
        } elseif (!empty($allowedMimes) && !in_array($file['type'], $allowedMimes)) {
            $uploadError = 'Tipo de arquivo não permitido.';
        } else {
            try {
                FileManager::createFromUpload($tenant, $file);
                \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . $tenant['uuid'] . '/files?upload=ok'));
            } catch (\Throwable $e) {
                $uploadError = $e->getMessage();
            }
        }
    } else {
        $uploadError = $file ? 'Erro no upload (código ' . ($file['error'] ?? '?') . ').' : 'Nenhum arquivo enviado.';
    }
    $tenantId = (int) $tenant['id'];
    $page = 1;
    $perPage = 30;
    $offset = 0;
    $files = FileManager::listByTenant($tenantId, $perPage, $offset);
    $totalFiles = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM files WHERE tenant_id = ?', [$tenantId])['n'];
    $totalPages = $totalFiles ? (int) ceil($totalFiles / $perPage) : 1;
    require __DIR__ . '/views/tenant_files.php';
    exit;
}

if (preg_match('#^/tenants/(' . $uuidRegex . ')/files$#', $path, $m)) {
    $tenant = Tenant::getByUuid($m[1]);
    if (!$tenant) {
        \NanoCDN\redirect(\NanoCDN\base_url('admin'));
    }
    $tenantId = (int) $tenant['id'];
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 30;
    $offset = ($page - 1) * $perPage;
    $files = FileManager::listByTenant($tenantId, $perPage, $offset);
    $totalFiles = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM files WHERE tenant_id = ?', [$tenantId])['n'];
    $totalPages = $totalFiles ? (int) ceil($totalFiles / $perPage) : 1;
    require __DIR__ . '/views/tenant_files.php';
    exit;
}

if (preg_match('#^/tenants/(' . $uuidRegex . ')/keys/delete/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $tenant = Tenant::getByUuid($m[1]);
    if ($tenant) {
        $keyId = (int) $m[2];
        Tenant::revokeApiKey($keyId, (int) $tenant['id']);
    }
    $redirectUuid = $tenant['uuid'] ?? $m[1];
    \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . $redirectUuid));
}

if (preg_match('#^/files/delete/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $fileId = (int) $m[1];
    $file = Database::fetchOne('SELECT tenant_id FROM files WHERE id = ?', [$fileId]);
    if ($file) {
        FileManager::delete($fileId);
        $t = Tenant::getById((int) $file['tenant_id']);
        \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . ($t['uuid'] ?? $file['tenant_id'])));
    }
    \NanoCDN\redirect(\NanoCDN\base_url('admin'));
}

require __DIR__ . '/views/admin_dashboard.php';
