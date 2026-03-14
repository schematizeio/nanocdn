<?php
/**
 * NanoCDN - Instalação via navegador
 * Acesse: http://seu-dominio/install.php
 * Após instalar, remova ou renomeie este arquivo.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$alreadyInstalled = false;
$envFile = is_file(__DIR__ . '/.env') ? __DIR__ . '/.env' : __DIR__ . '/config/.env.installed';
if (is_file($envFile)) {
    $env = [];
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($k, $v) = explode('=', $line, 2);
            $env[trim($k)] = trim($v, " \t\"'");
        }
    }
    $dsn = 'mysql:host=' . ($env['NANOCDN_DB_HOST'] ?? 'localhost') . ';dbname=' . ($env['NANOCDN_DB_NAME'] ?? 'nanocdn') . ';charset=utf8mb4';
    try {
        $pdo = new PDO($dsn, $env['NANOCDN_DB_USER'] ?? '', $env['NANOCDN_DB_PASS'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $n = $pdo->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
        if ((int) $n > 0) {
            $alreadyInstalled = true;
        }
    } catch (Throwable $e) {
        // DB inacessível, mostrar formulário ou erro
    }
}

$step = (int) ($_GET['step'] ?? 1);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 1) {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $name = trim($_POST['db_name'] ?? '');
        $user = trim($_POST['db_user'] ?? '');
        $pass = $_POST['db_pass'] ?? '';
        $admin_email = trim($_POST['admin_email'] ?? '');
        $admin_pass = $_POST['admin_pass'] ?? '';
        $base_url = trim($_POST['base_url'] ?? '');
        if (!$name || !$user || !$admin_email || strlen($admin_pass) < 6) {
            $error = 'Preencha todos os campos. Senha admin com pelo menos 6 caracteres.';
        } else {
            try {
                $dsn = "mysql:host=$host;charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `$name`");
                $sql = file_get_contents(__DIR__ . '/schema.sql');
                $pdo->exec($sql);
                $hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO admin_users (email, password_hash, name) VALUES (?, ?, ?)');
                $stmt->execute([$admin_email, $hash, 'Administrador']);
                $env = "NANOCDN_DB_HOST=$host\nNANOCDN_DB_NAME=$name\nNANOCDN_DB_USER=$user\nNANOCDN_DB_PASS=" . str_replace(["\\", "'"], ["\\\\", "\\'"], $pass) . "\n";
                if ($base_url !== '') {
                    $env .= "NANOCDN_BASE_URL=" . str_replace(["\n", "\r"], '', $base_url) . "\n";
                }
                file_put_contents(__DIR__ . '/.env', $env);
                $storageDir = __DIR__ . '/storage';
                if (!is_dir($storageDir)) {
                    mkdir($storageDir, 0755, true);
                }
                $htaccess = $storageDir . '/.htaccess';
                if (!is_file($htaccess)) {
                    file_put_contents($htaccess, "# Bloqueia acesso direto ao storage\n<IfModule mod_authz_core.c>\n    Require all denied\n</IfModule>\n");
                }
                $success = 'Instalação concluída. Faça login em /admin/login. Recomendado: remova ou renomeie install.php.';
                $step = 2;
            } catch (PDOException $e) {
                $error = 'Erro no banco: ' . $e->getMessage();
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NanoCDN - Instalação</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: system-ui, sans-serif; max-width: 480px; margin: 2rem auto; padding: 0 1rem; }
        h1 { font-size: 1.5rem; }
        label { display: block; margin-top: 0.75rem; font-weight: 500; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 0.5rem; margin-top: 0.25rem; }
        button { margin-top: 1rem; padding: 0.5rem 1rem; background: #333; color: #fff; border: none; cursor: pointer; }
        .error { background: #fee; color: #c00; padding: 0.5rem; margin: 0.5rem 0; }
        .success { background: #efe; color: #060; padding: 0.5rem; margin: 0.5rem 0; }
        a { color: #06c; }
    </style>
</head>
<body>
    <h1>NanoCDN – Instalação</h1>
    <?php if (!empty($alreadyInstalled)): ?>
        <div class="success">Já instalado. Use o painel administrativo.</div>
        <p><a href="admin/login">Ir para login</a></p>
    <?php elseif ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if (empty($alreadyInstalled) && $step === 1): ?>
        <form method="post">
            <h2>Banco de dados</h2>
            <label>Host <input type="text" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? 'localhost') ?>"></label>
            <label>Nome do banco <input type="text" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'nanocdn') ?>"></label>
            <label>Usuário <input type="text" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? '') ?>"></label>
            <label>Senha <input type="password" name="db_pass"></label>
            <h2>Administrador</h2>
            <label>E-mail <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? '') ?>"></label>
            <label>Senha (mín. 6 caracteres) <input type="password" name="admin_pass"></label>
            <h2>URL base (opcional)</h2>
            <label>URL base <input type="url" name="base_url" placeholder="https://cdn.seudominio.com" value="<?= htmlspecialchars($_POST['base_url'] ?? '') ?>"> <small>Use se estiver atrás de proxy ou em subdomínio; deixe vazio para detectar automaticamente.</small></label>
            <button type="submit">Instalar</button>
        </form>
    <?php elseif (empty($alreadyInstalled) && $step === 2): ?>
        <p><a href="admin/login">Ir para login admin</a></p>
    <?php endif; ?>
</body>
</html>
