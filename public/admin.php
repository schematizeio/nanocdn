<?php
/**
 * NanoCDN - Admin (login, tenants, files, checker)
 * Deve ser usado via index.php; se invocado direto (ex.: /admin/login sem query), repassa ao front controller.
 */
if (!class_exists('NanoCDN\Database', false)) {
    $uri = $_SERVER['REQUEST_URI'] ?? '/admin';
    $path = strpos($uri, '?') !== false ? strstr($uri, '?', true) : $uri;
    $path = '/' . trim($path, '/');
    if ($path === '' || $path === '/') {
        $path = '/admin';
    }
    $_GET['__path'] = $path;
    require __DIR__ . '/index.php';
    return;
}

use NanoCDN\Auth;
use NanoCDN\base_url;
use NanoCDN\Database;
use NanoCDN\FileManager;
use NanoCDN\ImageConverter;
use NanoCDN\Migrations;
use NanoCDN\redirect;
use NanoCDN\Settings;
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

// Registro (com token de convite ou aberto se allow_registration)
if ($path === '/register') {
    $token = trim($_GET['token'] ?? '');
    $invite = null;
    if ($token !== '') {
        try {
            $invite = Database::fetchOne('SELECT id, token, email FROM admin_invites WHERE token = ? AND used_at IS NULL', [$token]);
            if (!$invite) {
                $invite = false; // token inválido ou já usado
            }
        } catch (\Throwable $e) {
            $invite = false; // tabela pode não existir ainda
        }
    }
    $allowOpen = \NanoCDN\allow_registration();
    if ($token === '' && !$allowOpen) {
        \NanoCDN\redirect(\NanoCDN\base_url('admin/login'));
    }
    $regError = '';
    $regSuccess = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        $postToken = trim($_POST['invite_token'] ?? '');
        if ($email === '' || strlen($password) < 6) {
            $regError = 'E-mail e senha (mín. 6 caracteres) são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $regError = 'E-mail inválido.';
        } else {
            $inviteToConsume = null;
            if ($postToken !== '') {
                try {
                    $inviteToConsume = Database::fetchOne('SELECT id, email FROM admin_invites WHERE token = ? AND used_at IS NULL', [$postToken]);
                } catch (\Throwable $e) {
                    $inviteToConsume = null;
                }
                if (!$inviteToConsume) {
                    $regError = 'Convite inválido ou já utilizado.';
                }
            } elseif (!$allowOpen) {
                $regError = 'Cadastro só é permitido com convite.';
            }
            if ($regError === '' && Database::fetchOne('SELECT id FROM admin_users WHERE email = ?', [$email])) {
                $regError = 'Já existe uma conta com este e-mail.';
            }
            if ($regError === '') {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                Database::run('INSERT INTO admin_users (email, password_hash, name) VALUES (?, ?, ?)', [$email, $hash, $name !== '' ? $name : $email]);
                if (!empty($inviteToConsume['id'])) {
                    try {
                        Database::run('UPDATE admin_invites SET used_at = NOW() WHERE id = ?', [$inviteToConsume['id']]);
                    } catch (\Throwable $e) {
                        // tabela pode não existir
                    }
                }
                $regSuccess = true;
                \NanoCDN\redirect(\NanoCDN\base_url('admin/login?registered=1'));
            }
        }
    }
    require __DIR__ . '/views/admin_register.php';
    exit;
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

if ($path === '/migrations') {
    $pending = Migrations::getPending();
    $executedIds = Migrations::getExecutedIds();
    $migrationResult = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $migrationResult = Migrations::runPending();
    }
    require __DIR__ . '/views/admin_migrations.php';
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

if ($path === '/conversion') {
    $globalConv = ImageConverter::getGlobalConversionOptions();
    $saved = false;
    $convError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $enabled = isset($_POST['conversion_enabled']) ? '1' : '0';
        Settings::set('conversion_enabled', $enabled);
        $sizesRaw = trim($_POST['conversion_sizes'] ?? '');
        $sizesArr = [];
        foreach (preg_split('/\r?\n/', $sizesRaw) as $line) {
            $line = trim($line);
            if (preg_match('/^(\d+)\s*[x×]\s*(\d+)$/i', str_replace(' ', '', $line), $m)) {
                $sizesArr[] = ['w' => (int) $m[1], 'h' => (int) $m[2]];
            }
        }
        Settings::set('conversion_sizes', $sizesArr ? json_encode($sizesArr) : null);
        $formats = is_array($_POST['conversion_formats'] ?? null) ? $_POST['conversion_formats'] : [];
        Settings::set('conversion_formats', $formats ? json_encode($formats) : null);
        $qual = isset($_POST['conversion_quality']) ? (int) $_POST['conversion_quality'] : 85;
        $qual = max(1, min(100, $qual));
        Settings::set('conversion_quality', (string) $qual);
        $saved = true;
        $globalConv = ImageConverter::getGlobalConversionOptions();
    }
    require __DIR__ . '/views/admin_conversion.php';
    exit;
}

if ($path === '/settings' || $path === '/configuracoes') {
    Auth::requireAdmin();
    $saved = false;
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $name = trim($_POST['app_name'] ?? '');
        Settings::set('app_name', $name !== '' ? $name : null);
        $logo = trim($_POST['app_logo_url'] ?? '');
        Settings::set('app_logo_url', $logo !== '' ? $logo : null);
        Settings::set('allow_registration', !empty($_POST['allow_registration']) ? '1' : '0');
        $saved = true;
    }
    $appName = \NanoCDN\app_name();
    $appNameValue = Settings::get('app_name');
    $appLogoUrl = \NanoCDN\app_logo_url();
    $allowRegistration = \NanoCDN\allow_registration();
    require __DIR__ . '/views/admin_settings.php';
    exit;
}

// CRUD usuários admin
if ($path === '/users') {
    $users = Database::fetchAll('SELECT id, email, name, created_at FROM admin_users ORDER BY name, email');
    require __DIR__ . '/views/admin_users.php';
    exit;
}

if ($path === '/users/new') {
    $userError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($email === '' || strlen($password) < 6) {
            $userError = 'E-mail e senha (mín. 6 caracteres) são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $userError = 'E-mail inválido.';
        } elseif (Database::fetchOne('SELECT id FROM admin_users WHERE email = ?', [$email])) {
            $userError = 'Já existe usuário com este e-mail.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            Database::run('INSERT INTO admin_users (email, password_hash, name) VALUES (?, ?, ?)', [$email, $hash, $name !== '' ? $name : $email]);
            \NanoCDN\redirect(\NanoCDN\base_url('admin/users?created=1'));
        }
    }
    require __DIR__ . '/views/admin_user_form.php';
    exit;
}

if (preg_match('#^/users/(\d+)/edit$#', $path, $m)) {
    $userId = (int) $m[1];
    $user = Database::fetchOne('SELECT id, email, name FROM admin_users WHERE id = ?', [$userId]);
    if (!$user) {
        \NanoCDN\redirect(\NanoCDN\base_url('admin/users'));
    }
    $userError = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $name = trim($_POST['name'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        Database::run('UPDATE admin_users SET name = ? WHERE id = ?', [$name !== '' ? $name : $user['email'], $userId]);
        if ($newPassword !== '') {
            if (strlen($newPassword) < 6) {
                $userError = 'Nova senha deve ter pelo menos 6 caracteres.';
            } else {
                $hash = password_hash($newPassword, PASSWORD_DEFAULT);
                Database::run('UPDATE admin_users SET password_hash = ? WHERE id = ?', [$hash, $userId]);
            }
        }
        if ($userError === '') {
            \NanoCDN\redirect(\NanoCDN\base_url('admin/users?updated=1'));
        }
    }
    $user = Database::fetchOne('SELECT id, email, name FROM admin_users WHERE id = ?', [$userId]);
    require __DIR__ . '/views/admin_user_form.php';
    exit;
}

if (preg_match('#^/users/(\d+)/delete$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $userId = (int) $m[1];
    $currentId = (int) (Auth::user()['id'] ?? 0);
    if ($userId !== $currentId) {
        Database::run('DELETE FROM admin_users WHERE id = ?', [$userId]);
    }
    \NanoCDN\redirect(\NanoCDN\base_url('admin/users'));
}

// Convites (cadastro por link ou e-mail)
if ($path === '/invites') {
    $inviteError = '';
    $inviteSuccess = '';
    $newLink = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        $action = $_POST['action'] ?? 'create';
        if ($action === 'create') {
            $email = trim($_POST['email'] ?? '');
            $token = bin2hex(random_bytes(32));
            $createdBy = (int) (Auth::user()['id'] ?? 0);
            try {
                Database::run('INSERT INTO admin_invites (token, email, created_by) VALUES (?, ?, ?)', [$token, $email !== '' ? $email : null, $createdBy]);
                $base = \NanoCDN\base_url('');
                $newLink = $base . 'admin/register?token=' . $token;
                $inviteSuccess = $email !== '' ? 'Convite criado. Envie o link abaixo ao destinatário (ou use o link gerado).' : 'Link de convite gerado (uso único).';
            } catch (\Throwable $e) {
                $inviteError = 'Erro ao criar convite: ' . $e->getMessage();
            }
        }
    }
    try {
        $invites = Database::fetchAll('SELECT i.id, i.token, i.email, i.used_at, i.created_at, u.name AS created_by_name FROM admin_invites i LEFT JOIN admin_users u ON u.id = i.created_by ORDER BY i.created_at DESC LIMIT 100');
    } catch (\Throwable $e) {
        $invites = [];
        if (empty($inviteError)) {
            $inviteError = 'Execute as migrações em Admin → Migrações para usar convites.';
        }
    }
    require __DIR__ . '/views/admin_invites.php';
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
    Auth::init();
    $newKey = null;
    if (!empty($_SESSION['nanocdn_new_api_key']) && ($_SESSION['nanocdn_new_api_key']['tenant_uuid'] ?? '') === $tenantUuid) {
        $newKey = $_SESSION['nanocdn_new_api_key']['key'];
        unset($_SESSION['nanocdn_new_api_key']);
    }
    if ($newKey === null) {
        $newKey = $_GET['new_key'] ?? null;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
        if (isset($_POST['action'])) {
            if ($_POST['action'] === 'regenerate_key') {
                $keyData = Tenant::createApiKey($tenantId, 'Generated ' . date('Y-m-d H:i'));
                Auth::init();
                $_SESSION['nanocdn_new_api_key'] = ['key' => $keyData['key'], 'tenant_uuid' => $tenantUuid];
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
    $rawSizes = $tenant['conversion_sizes'] ?? null;
    $tenantConversionSizes = $rawSizes !== null && $rawSizes !== '' ? (json_decode($rawSizes, true) ?: []) : [];
    $rawFormats = $tenant['conversion_formats'] ?? null;
    $tenantConversionFormats = $rawFormats !== null && $rawFormats !== '' ? (json_decode($rawFormats, true) ?: []) : [];
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

if (preg_match('#^/files/reconvert/(\d+)$#', $path, $m) && $_SERVER['REQUEST_METHOD'] === 'POST' && Auth::csrfVerify()) {
    $fileId = (int) $m[1];
    $file = Database::fetchOne('SELECT tenant_id FROM files WHERE id = ?', [$fileId]);
    if ($file) {
        FileManager::regenerateVariants($fileId);
        $t = Tenant::getById((int) $file['tenant_id']);
        \NanoCDN\redirect(\NanoCDN\base_url('admin/tenants/' . ($t['uuid'] ?? $file['tenant_id']) . '/files?reconverted=1'));
    }
    \NanoCDN\redirect(\NanoCDN\base_url('admin'));
}

require __DIR__ . '/views/admin_dashboard.php';
