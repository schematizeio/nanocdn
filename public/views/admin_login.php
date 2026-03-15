<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        body.admin { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-box { width: 100%; max-width: 360px; padding: 1.5rem; }
        .login-box h1 { margin: 0 0 1.5rem; font-size: 1.5rem; }
    </style>
</head>
<body class="admin">
    <div class="admin-card login-box">
        <h1><?= htmlspecialchars(\NanoCDN\app_name()) ?> – Login</h1>
        <?php if (!empty($_GET['expired'])): ?><div class="admin-alert admin-alert-info">Sessão expirada. Faça login novamente.</div><?php endif; ?>
        <?php if (!empty($error)): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
            <label>E-mail <input type="email" name="email" required autofocus></label>
            <label>Senha <input type="password" name="password" required></label>
            <button type="submit" class="admin-btn">Entrar</button>
        </form>
        <?php if (\NanoCDN\allow_registration()): ?>
        <p style="margin-top:1rem;font-size:0.9rem;"><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/register')) ?>">Criar conta</a></p>
        <?php endif; ?>
        <?php if (!empty($_GET['registered'])): ?><div class="admin-alert admin-alert-success" style="margin-top:1rem;">Conta criada. Faça login.</div><?php endif; ?>
    </div>
</body>
</html>
