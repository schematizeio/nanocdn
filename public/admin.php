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
        redirect(base_url('admin'));
    }
    $error = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!Auth::csrfVerify()) {
            $error = 'Requisição inválida. Tente novamente.';
        } else {
            $email = trim($_POST['email'] ?? '');
            $pass = $_POST['password'] ?? '';
            if (Auth::login($email, $pass)) {
                redirect(base_url('admin'));
            }
            $error = 'E-mail ou senha inválidos.';
        }
    }
    require __DIR__ . '/views/admin_login.php';
    exit;
}

if ($path === '/logout') {
    Auth::logout();
    redirect(base_url('admin/login'));
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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $repoRoot = defined('NANOCDN_ROOT') ? NANOCDN_ROOT : dirname(__DIR__);
        if (!is_dir($repoRoot . '/.git')) {
            $error = 'Este diretório não é um clone Git. Faça o deploy via clone (git clone) para usar atualização automática.';
        } else {
            $branch = 'master';
            $safeRoot = function_exists('escapeshellarg') ? escapeshellarg($repoRoot) : "'" . str_replace("'", "'\\''", $repoRoot) . "'";
            $safeBranch = function_exists('escapeshellarg') ? escapeshellarg($branch) : "'" . str_replace("'", "'\\''", $branch) . "'";
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
                        $error = 'Falha no git pull. Atualize manualmente: cd ' . htmlspecialchars($repoRoot) . ' && git pull origin main';
                        $output = trim($output) ?: 'Sem saída.';
                    }
                } else {
                    $output = "Atualização concluída (branch {$branch}).\n" . $output;
                }
            } else {
                $error = 'exec() desabilitada. Atualize manualmente: cd ' . htmlspecialchars($repoRoot) . ' && git pull origin main';
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

if ($path === '/' || $path === '') {
    $tenants = Tenant::all();
    $totalFiles = Database::fetchOne('SELECT COUNT(*) AS n FROM files');
    require __DIR__ . '/views/admin_dashboard.php';
    exit;
}

if (preg_match('#^/tenants/new$#', $path)) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $name = trim($_POST['name'] ?? '');
        $conversion = !empty($_POST['conversion_enabled']);
        if ($name !== '') {
            $t = Tenant::create($name, $conversion);
            $keyData = Tenant::createApiKey($t['id'], 'Default');
            redirect(base_url('admin/tenants/' . $t['id'] . '?new_key=' . urlencode($keyData['key'])));
        }
    }
    require __DIR__ . '/views/tenant_form.php';
    exit;
}

if (preg_match('#^/tenants/(\d+)$#', $path, $m)) {
    $tenantId = (int) $m[1];
    $tenant = Tenant::getById($tenantId);
    if (!$tenant) {
        header('Location: ' . base_url('admin'));
        exit;
    }
    $newKey = $_GET['new_key'] ?? null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'regenerate_key') {
                Tenant::createApiKey($tenantId, 'Generated ' . date('Y-m-d H:i'));
                redirect(base_url('admin/tenants/' . $tenantId));
            }
            if ($_POST['action'] === 'update') {
                Tenant::update($tenantId, [
                    'name' => trim($_POST['name'] ?? ''),
                    'active' => isset($_POST['active']) ? 1 : 0,
                    'conversion_enabled' => isset($_POST['conversion_enabled']) ? 1 : 0,
                ]);
                redirect(base_url('admin/tenants/' . $tenantId));
            }
        }
    }
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

if (preg_match('#^/tenants/(\d+)/files$#', $path, $m)) {
    $tenantId = (int) $m[1];
    $tenant = Tenant::getById($tenantId);
    if (!$tenant) {
        redirect(base_url('admin'));
    }
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 30;
    $offset = ($page - 1) * $perPage;
    $files = FileManager::listByTenant($tenantId, $perPage, $offset);
    $totalFiles = (int) Database::fetchOne('SELECT COUNT(*) AS n FROM files WHERE tenant_id = ?', [$tenantId])['n'];
    $totalPages = $totalFiles ? (int) ceil($totalFiles / $perPage) : 1;
    require __DIR__ . '/views/tenant_files.php';
    exit;
}

if (preg_match('#^/tenants/(\d+)/keys/delete/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $tenantId = (int) $m[1];
    $keyId = (int) $m[2];
    if (Tenant::revokeApiKey($keyId, $tenantId)) {
        redirect(base_url('admin/tenants/' . $tenantId));
    }
    redirect(base_url('admin/tenants/' . $tenantId));
}

if (preg_match('#^/files/delete/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $fileId = (int) $m[1];
    $file = Database::fetchOne('SELECT tenant_id FROM files WHERE id = ?', [$fileId]);
    if ($file) {
        FileManager::delete($fileId);
        redirect(base_url('admin/tenants/' . $file['tenant_id']));
    }
    redirect(base_url('admin'));
}

require __DIR__ . '/views/admin_dashboard.php';
