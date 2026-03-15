<?php
$invite = $invite ?? null;
$allowOpen = $allowOpen ?? false;
$regError = $regError ?? '';
$inviteEmail = is_array($invite) ? ($invite['email'] ?? '') : '';
$inviteToken = is_array($invite) ? ($invite['token'] ?? '') : (trim($_GET['token'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - <?= htmlspecialchars(\NanoCDN\app_name()) ?></title>
    <?php require __DIR__ . '/_admin_head.php'; ?>
    <style>
        body.admin { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .register-box { width: 100%; max-width: 400px; padding: 1.5rem; }
        .register-box h1 { margin: 0 0 1rem; font-size: 1.35rem; }
    </style>
</head>
<body class="admin">
    <div class="admin-card register-box">
        <h1>Criar conta – <?= htmlspecialchars(\NanoCDN\app_name()) ?></h1>
        <?php if ($invite === false): ?>
            <div class="admin-alert admin-alert-error">Link de convite inválido ou já utilizado.</div>
            <p><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/login')) ?>" class="admin-btn">Ir para login</a></p>
        <?php else: ?>
            <?php if ($regError): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($regError) ?></div><?php endif; ?>
            <form method="post" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(\NanoCDN\Auth::csrfToken()) ?>">
                <?php if ($inviteToken !== ''): ?><input type="hidden" name="invite_token" value="<?= htmlspecialchars($inviteToken) ?>"><?php endif; ?>
                <label>E-mail <input type="email" name="email" value="<?= htmlspecialchars($inviteEmail) ?>" required autofocus></label>
                <label>Nome <input type="text" name="name" placeholder="Opcional"></label>
                <label>Senha (mín. 6 caracteres) <input type="password" name="password" required></label>
                <button type="submit" class="admin-btn admin-btn-primary">Criar conta</button>
            </form>
            <p style="margin-top:1rem;font-size:0.9rem;"><a href="<?= htmlspecialchars(\NanoCDN\base_url('admin/login')) ?>">Já tenho conta – entrar</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
